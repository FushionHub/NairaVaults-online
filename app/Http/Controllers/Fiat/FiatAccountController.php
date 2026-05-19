<?php

namespace App\Http\Controllers\Fiat;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\Transaction;
use App\Services\Payments\FlutterwaveService;
use App\Services\Payments\KorapayService;
use App\Services\Payments\PayPalService;
use App\Services\Payments\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FiatAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $accounts = FiatAccount::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->get();

        return response()->json(['accounts' => $accounts]);
    }

    public function createVirtualAccount(Request $request, KorapayService $korapayService): JsonResponse
    {
        $validated = $request->validate([
            'currency' => ['required', 'string', 'in:NGN,USD,EUR,GBP,GHS'],
        ]);

        $user = $request->user();

        $account = FiatAccount::firstOrCreate(
            ['user_id' => $user->id, 'currency' => $validated['currency']],
            ['balance' => '0.00000000']
        );

        if ($account->virtual_account_number) {
            return response()->json(['account' => $account]);
        }

        $result = $korapayService->createVirtualAccount([
            'account_name' => $user->display_name ?? $user->email,
            'reference' => 'VA-'.$user->id.'-'.$validated['currency'],
            'customer_name' => $user->display_name ?? $user->email,
            'customer_email' => $user->email,
        ]);

        if (isset($result['data'])) {
            $account->update([
                'virtual_account_number' => $result['data']['account_number'] ?? null,
                'virtual_account_bank' => $result['data']['bank_name'] ?? null,
                'virtual_account_name' => $result['data']['account_name'] ?? null,
                'gateway' => 'korapay',
            ]);
        }

        return response()->json(['account' => $account->fresh()], 201);
    }

    public function deposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'currency' => ['required', 'string', 'in:NGN,USD,EUR,GBP,GHS'],
            'gateway' => ['required', 'string', 'in:korapay,paystack,flutterwave,paypal'],
        ]);

        $user = $request->user();
        $reference = 'DEP-'.Str::uuid();

        $account = FiatAccount::firstOrCreate(
            ['user_id' => $user->id, 'currency' => $validated['currency']],
            ['balance' => '0.00000000']
        );

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'deposit',
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'pending',
            'reference' => $reference,
            'direction' => 'credit',
        ]);

        $service = match ($validated['gateway']) {
            'korapay' => app(KorapayService::class),
            'paystack' => app(PaystackService::class),
            'flutterwave' => app(FlutterwaveService::class),
            'paypal' => app(PayPalService::class),
        };

        $result = match ($validated['gateway']) {
            'korapay' => $service->initiateCharge([
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'reference' => $reference,
                'email' => $user->email,
                'name' => $user->display_name ?? $user->email,
            ]),
            'paystack' => $service->initializeTransaction([
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'reference' => $reference,
                'email' => $user->email,
            ]),
            'flutterwave' => $service->initiatePayment([
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'reference' => $reference,
                'email' => $user->email,
                'name' => $user->display_name ?? $user->email,
            ]),
            'paypal' => $service->createOrder([
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'reference' => $reference,
            ]),
        };

        return response()->json([
            'reference' => $reference,
            'gateway_data' => $result,
        ]);
    }

    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'currency' => ['required', 'string', 'in:NGN,USD,EUR,GBP,GHS'],
            'bank_code' => ['required_if:currency,NGN', 'string'],
            'account_number' => ['required_if:currency,NGN', 'string'],
            'gateway' => ['required', 'string', 'in:korapay,paystack,flutterwave,paypal'],
        ]);

        $user = $request->user();

        $lockKey = "withdraw:{$user->id}:{$validated['amount']}:{$validated['account_number']}";
        if (! Cache::add($lockKey, 1, 60)) {
            return response()->json([
                'error' => 'Duplicate withdrawal detected. Please wait 60 seconds.',
                'code' => 'DUPLICATE_WITHDRAWAL',
            ], 429);
        }

        $account = FiatAccount::where('user_id', $user->id)
            ->where('currency', $validated['currency'])
            ->firstOrFail();

        if (! $account->hasBalance($validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $reference = 'WTH-'.Str::uuid();
        $account->subtractBalance($validated['amount']);

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'processing',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => [
                'bank_code' => $validated['bank_code'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
            ],
        ]);

        return response()->json([
            'message' => 'Withdrawal initiated',
            'reference' => $reference,
        ]);
    }

    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'from_currency' => ['required', 'string', 'in:NGN,USD,EUR,GBP,GHS'],
            'to_account_id' => ['nullable', 'integer'],
            'to_email' => ['nullable', 'email'],
        ]);

        $user = $request->user();
        $fromAccount = FiatAccount::where('user_id', $user->id)
            ->where('currency', $validated['from_currency'])
            ->firstOrFail();

        if (! $fromAccount->hasBalance($validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $reference = 'TRF-'.Str::uuid();
        $fromAccount->subtractBalance($validated['amount']);

        if (! empty($validated['to_account_id'])) {
            $toAccount = FiatAccount::findOrFail($validated['to_account_id']);
            $toAccount->addBalance($validated['amount']);
        }

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $fromAccount->id,
            'type' => 'transfer',
            'amount' => $validated['amount'],
            'currency' => $validated['from_currency'],
            'status' => 'completed',
            'reference' => $reference,
            'direction' => 'debit',
        ]);

        return response()->json([
            'message' => 'Transfer completed',
            'reference' => $reference,
        ]);
    }

    public function banks(PaystackService $paystackService): JsonResponse
    {
        $banks = $paystackService->getBankList();

        return response()->json(['banks' => $banks['data'] ?? []]);
    }

    public function resolveBankAccount(Request $request, PaystackService $paystackService): JsonResponse
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'size:10'],
            'bank_code' => ['required', 'string'],
        ]);

        $result = $paystackService->resolveAccountNumber(
            $validated['account_number'],
            $validated['bank_code']
        );

        return response()->json($result);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($transactions);
    }
}

<?php

namespace App\Http\Controllers\Wealth;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $loans = Loan::where('user_id', $request->user()->id)->get();

        return response()->json(['loans' => $loans]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'currency' => ['required', 'string', 'in:NGN,USD'],
            'tenure_months' => ['required', 'integer', 'min:1', 'max:24'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        if (! $user->isKycVerified()) {
            return response()->json(['error' => 'KYC verification required'], 403);
        }

        $hasOverdue = Loan::where('user_id', $user->id)->where('status', 'overdue')->exists();
        if ($hasOverdue) {
            return response()->json(['error' => 'Cannot apply for loan with overdue loans'], 400);
        }

        $interestRate = '0.0500';
        $totalRepayable = bcmul($validated['amount'], bcadd('1', bcmul($interestRate, (string) $validated['tenure_months'], 8), 8), 8);

        $schedule = [];
        $monthlyPayment = bcdiv($totalRepayable, (string) $validated['tenure_months'], 8);
        for ($i = 1; $i <= $validated['tenure_months']; $i++) {
            $schedule[] = [
                'month' => $i,
                'due_date' => now()->addMonths($i)->toDateString(),
                'amount' => $monthlyPayment,
                'paid' => false,
            ];
        }

        $loan = Loan::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'tenure_months' => $validated['tenure_months'],
            'purpose' => $validated['purpose'],
            'interest_rate' => $interestRate,
            'total_repayable' => $totalRepayable,
            'status' => 'approved',
            'disbursed_at' => now(),
            'repayment_schedule' => $schedule,
        ]);

        $account = FiatAccount::firstOrCreate(
            ['user_id' => $user->id, 'currency' => $validated['currency']],
            ['balance' => '0.00000000']
        );

        $account->addBalance((string) $validated['amount']);

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'loan_disbursement',
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'completed',
            'reference' => 'LOAN-' . Str::uuid(),
            'direction' => 'credit',
        ]);

        return response()->json(['loan' => $loan], 201);
    }

    public function repay(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $loan = Loan::where('user_id', $request->user()->id)->findOrFail($id);
        $account = FiatAccount::where('user_id', $request->user()->id)
            ->where('currency', $loan->currency)
            ->firstOrFail();

        if (! $account->hasBalance((string) $validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $account->subtractBalance((string) $validated['amount']);

        Transaction::create([
            'user_id' => $request->user()->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'loan_repayment',
            'amount' => $validated['amount'],
            'currency' => $loan->currency,
            'status' => 'completed',
            'reference' => 'LREP-' . Str::uuid(),
            'direction' => 'debit',
        ]);

        return response()->json(['message' => 'Repayment recorded']);
    }
}

<?php

namespace App\Http\Controllers\Cards;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\VirtualCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class VirtualCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cards = VirtualCard::where('user_id', $request->user()->id)->get();

        return response()->json(['cards' => $cards]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'card_type' => ['required', 'string', 'in:visa,verve,mastercard'],
            'currency' => ['required', 'string', 'in:USD,NGN'],
            'fiat_account_id' => ['required', 'integer', 'exists:fiat_accounts,id'],
        ]);

        $user = $request->user();

        $card = VirtualCard::create([
            'user_id' => $user->id,
            'fiat_account_id' => $validated['fiat_account_id'],
            'masked_pan' => '****-****-****-' . rand(1000, 9999),
            'expiry_month' => now()->addYears(3)->format('m'),
            'expiry_year' => now()->addYears(3)->format('Y'),
            'cvv_reference' => Crypt::encryptString((string) rand(100, 999)),
            'card_holder_name' => $user->display_name ?? $user->email,
            'card_type' => $validated['card_type'],
            'currency' => $validated['currency'],
        ]);

        return response()->json(['card' => $card], 201);
    }

    public function update(Request $request, int $cardId): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:fund,freeze,unfreeze,terminate'],
            'amount' => ['nullable', 'numeric', 'min:1'],
        ]);

        $card = VirtualCard::where('user_id', $request->user()->id)->findOrFail($cardId);

        switch ($validated['action']) {
            case 'fund':
                $amount = (string) $validated['amount'];
                $fiatAccount = FiatAccount::findOrFail($card->fiat_account_id);

                if (! $fiatAccount->hasBalance($amount)) {
                    return response()->json(['error' => 'Insufficient balance'], 400);
                }

                $fiatAccount->subtractBalance($amount);
                $card->update(['balance' => bcadd($card->balance, $amount, 8)]);
                break;
            case 'freeze':
                $card->update(['status' => 'frozen']);
                break;
            case 'unfreeze':
                $card->update(['status' => 'active']);
                break;
            case 'terminate':
                $card->update(['status' => 'terminated']);
                break;
        }

        return response()->json(['card' => $card->fresh()]);
    }
}

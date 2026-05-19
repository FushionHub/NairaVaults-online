<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsPlan extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'user_id', 'fiat_account_id', 'crypto_wallet_id', 'name',
        'amount', 'currency', 'interest_rate', 'start_date',
        'maturity_date', 'status', 'penalty_rate',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'interest_rate' => 'string',
            'penalty_rate' => 'string',
            'start_date' => 'date',
            'maturity_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fiatAccount(): BelongsTo
    {
        return $this->belongsTo(FiatAccount::class);
    }

    public function cryptoWallet(): BelongsTo
    {
        return $this->belongsTo(CryptoWallet::class);
    }
}

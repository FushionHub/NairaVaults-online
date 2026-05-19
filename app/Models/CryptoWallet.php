<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoWallet extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'user_id', 'coin_symbol', 'public_address',
        'encrypted_priv_key', 'privy_wallet_id', 'imported', 'balance',
    ];

    protected $hidden = ['encrypted_priv_key'];

    protected function casts(): array
    {
        return [
            'balance' => 'string',
            'imported' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function safeSelect(): array
    {
        return ['id', 'user_id', 'coin_symbol', 'public_address', 'privy_wallet_id', 'imported', 'balance', 'created_at', 'updated_at'];
    }
}

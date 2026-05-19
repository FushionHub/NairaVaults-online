<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pOffer extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'creator_user_id', 'counterparty_user_id', 'coin_symbol',
        'amount', 'rate_per_unit', 'total_fiat', 'currency',
        'direction', 'payment_method', 'status',
        'escrow_reference', 'dispute_raised_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'rate_per_unit' => 'string',
            'total_fiat' => 'string',
            'dispute_raised_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterparty_user_id');
    }
}

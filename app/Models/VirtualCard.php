<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'fiat_account_id', 'masked_pan', 'expiry_month',
        'expiry_year', 'cvv_reference', 'card_holder_name',
        'issuer_card_id', 'card_type', 'status', 'balance', 'currency',
    ];

    protected $hidden = ['cvv_reference'];

    protected function casts(): array
    {
        return ['balance' => 'string'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fiatAccount(): BelongsTo
    {
        return $this->belongsTo(FiatAccount::class);
    }
}

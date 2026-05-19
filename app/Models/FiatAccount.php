<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiatAccount extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'user_id', 'currency', 'balance', 'virtual_account_number',
        'virtual_account_bank', 'virtual_account_name', 'gateway', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'string',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transactionable_id')
            ->where('transactionable_type', self::class);
    }

    public function virtualCards(): HasMany
    {
        return $this->hasMany(VirtualCard::class);
    }
}

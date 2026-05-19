<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'user_id', 'transactionable_type', 'transactionable_id',
        'type', 'amount', 'currency', 'fee', 'status',
        'reference', 'gateway_reference', 'direction', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'fee' => 'string',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'user_id', 'amount', 'currency', 'tenure_months', 'purpose',
        'interest_rate', 'total_repayable', 'status',
        'disbursed_at', 'repayment_schedule',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'interest_rate' => 'string',
            'total_repayable' => 'string',
            'disbursed_at' => 'datetime',
            'repayment_schedule' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

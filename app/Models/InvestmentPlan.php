<?php

namespace App\Models;

use App\Traits\UsesDecimalArithmetic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentPlan extends Model
{
    use HasFactory, UsesDecimalArithmetic;

    protected $fillable = [
        'user_id', 'plan_name', 'amount', 'currency',
        'annual_yield', 'current_value', 'start_date',
        'maturity_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'string',
            'annual_yield' => 'string',
            'current_value' => 'string',
            'start_date' => 'date',
            'maturity_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

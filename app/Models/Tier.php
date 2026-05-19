<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description',
        'daily_transfer_limit', 'daily_withdrawal_limit', 'monthly_limit',
        'upgrade_fee', 'benefits', 'supported_currencies', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'daily_transfer_limit' => 'string',
            'daily_withdrawal_limit' => 'string',
            'monthly_limit' => 'string',
            'upgrade_fee' => 'string',
            'benefits' => 'array',
            'supported_currencies' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

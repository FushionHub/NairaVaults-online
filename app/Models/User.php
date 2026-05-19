<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email', 'phone', 'password', 'display_name', 'profile_photo',
        'preferred_currency', 'kyc_status', 'two_factor_enabled',
        'two_factor_secret', 'two_factor_recovery_codes',
        'google_id', 'google_token', 'google_refresh_token',
        'privy_did', 'dojah_id', 'account_type',
        'referral_code', 'referred_by', 'tier_id',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'google_token', 'google_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function fiatAccounts(): HasMany
    {
        return $this->hasMany(FiatAccount::class);
    }

    public function cryptoWallets(): HasMany
    {
        return $this->hasMany(CryptoWallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function kycRecord(): HasOne
    {
        return $this->hasOne(KycRecord::class);
    }

    public function virtualCards(): HasMany
    {
        return $this->hasMany(VirtualCard::class);
    }

    public function savingsPlans(): HasMany
    {
        return $this->hasMany(SavingsPlan::class);
    }

    public function investmentPlans(): HasMany
    {
        return $this->hasMany(InvestmentPlan::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function stakingPositions(): HasMany
    {
        return $this->hasMany(StakingPosition::class);
    }

    public function portfolioSnapshots(): HasMany
    {
        return $this->hasMany(PortfolioSnapshot::class);
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }
}

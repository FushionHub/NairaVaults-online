<?php

namespace Database\Seeders;

use App\Models\Tier;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Free tier with essential features',
                'daily_transfer_limit' => '50000.00000000',
                'daily_withdrawal_limit' => '20000.00000000',
                'monthly_limit' => '500000.00000000',
                'upgrade_fee' => '0.00000000',
                'benefits' => json_encode([
                    'Fiat deposits and withdrawals',
                    'Basic crypto wallet',
                    'P2P trading',
                    'Email support',
                ]),
                'supported_currencies' => json_encode(['NGN']),
                'sort_order' => 1,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'description' => 'Enhanced limits and multi-currency',
                'daily_transfer_limit' => '200000.00000000',
                'daily_withdrawal_limit' => '100000.00000000',
                'monthly_limit' => '2000000.00000000',
                'upgrade_fee' => '5000.00000000',
                'benefits' => json_encode([
                    'All Basic features',
                    'Multi-currency accounts (NGN, USD)',
                    'Virtual cards',
                    'Savings plans',
                    'Priority email support',
                ]),
                'supported_currencies' => json_encode(['NGN', 'USD']),
                'sort_order' => 2,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'description' => 'Premium features with higher limits',
                'daily_transfer_limit' => '1000000.00000000',
                'daily_withdrawal_limit' => '500000.00000000',
                'monthly_limit' => '10000000.00000000',
                'upgrade_fee' => '20000.00000000',
                'benefits' => json_encode([
                    'All Silver features',
                    'Multi-currency (NGN, USD, EUR, GBP)',
                    'Investment plans',
                    'Binary trading',
                    'AI assistant',
                    'Phone support',
                ]),
                'supported_currencies' => json_encode(['NGN', 'USD', 'EUR', 'GBP']),
                'sort_order' => 3,
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'description' => 'Elite features for power users',
                'daily_transfer_limit' => '5000000.00000000',
                'daily_withdrawal_limit' => '2000000.00000000',
                'monthly_limit' => '50000000.00000000',
                'upgrade_fee' => '50000.00000000',
                'benefits' => json_encode([
                    'All Gold features',
                    'All currencies (NGN, USD, EUR, GBP, GHS)',
                    'Loan access',
                    'Staking rewards',
                    'Multi-sig wallets',
                    'Dedicated account manager',
                ]),
                'supported_currencies' => json_encode(['NGN', 'USD', 'EUR', 'GBP', 'GHS']),
                'sort_order' => 4,
            ],
            [
                'name' => 'Diamond',
                'slug' => 'diamond',
                'description' => 'Ultimate tier with unlimited features',
                'daily_transfer_limit' => '999999999.00000000',
                'daily_withdrawal_limit' => '999999999.00000000',
                'monthly_limit' => '999999999.00000000',
                'upgrade_fee' => '100000.00000000',
                'benefits' => json_encode([
                    'All Platinum features',
                    'Unlimited transfers',
                    'Zero fees on transfers',
                    'Priority trade execution',
                    'Custom investment plans',
                    'White-glove support',
                    'Business account features',
                ]),
                'supported_currencies' => json_encode(['NGN', 'USD', 'EUR', 'GBP', 'GHS']),
                'sort_order' => 5,
            ],
        ];

        foreach ($tiers as $tier) {
            Tier::updateOrCreate(['slug' => $tier['slug']], $tier);
        }
    }
}

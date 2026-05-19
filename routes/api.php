<?php

use App\Http\Controllers\AI\AIAssistantController;
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TrustedDeviceController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Cards\VirtualCardController;
use App\Http\Controllers\Crypto\CryptoTransactionController;
use App\Http\Controllers\Crypto\MarketController;
use App\Http\Controllers\Crypto\MultiSigController;
use App\Http\Controllers\Crypto\PriceAlertController;
use App\Http\Controllers\Crypto\RecurringPurchaseController;
use App\Http\Controllers\Crypto\StakingController;
use App\Http\Controllers\Crypto\SwapController;
use App\Http\Controllers\Crypto\WalletController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Fiat\ScheduledTransferController;
use App\Http\Controllers\Fiat\FiatAccountController;
use App\Http\Controllers\Kyc\KycController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Receipt\ReceiptController;
use App\Http\Controllers\Referral\ReferralController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Support\SupportController;
use App\Http\Controllers\Tier\TierController;
use App\Http\Controllers\Trading\BinaryTradingController;
use App\Http\Controllers\Trading\P2PController;
use App\Http\Controllers\Wealth\InvestmentController;
use App\Http\Controllers\Wealth\LoanController;
use App\Http\Controllers\Wealth\SavingsController;
use App\Http\Controllers\Webhooks\FlutterwaveWebhookController;
use App\Http\Controllers\Webhooks\KorapayWebhookController;
use App\Http\Controllers\Webhooks\PayPalWebhookController;
use App\Http\Controllers\Webhooks\PaystackWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth - Public
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
        Route::get('/google/redirect', [OAuthController::class, 'redirectToGoogle']);
        Route::get('/google/callback', [OAuthController::class, 'handleGoogleCallback']);
        Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
    });

    // Auth - Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [LoginController::class, 'logout']);
        Route::post('/auth/verify-email', [PasswordResetController::class, 'verifyEmail']);

        // 2FA
        Route::post('/auth/2fa/setup', [TwoFactorController::class, 'setup']);
        Route::post('/auth/2fa/enable', [TwoFactorController::class, 'enable']);
        Route::post('/auth/2fa/disable', [TwoFactorController::class, 'disable']);

        // Trusted Devices
        Route::get('/auth/trusted-devices', [TrustedDeviceController::class, 'index']);
        Route::post('/auth/trusted-devices', [TrustedDeviceController::class, 'store']);
        Route::delete('/auth/trusted-devices/{id}', [TrustedDeviceController::class, 'destroy']);

        // KYC
        Route::get('/kyc/status', [KycController::class, 'status']);
        Route::post('/kyc/submit', [KycController::class, 'submit']);
        Route::post('/kyc/submit-business', [KycController::class, 'submitBusiness']);

        // Dashboard
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

        // Settings
        Route::get('/settings', [SettingsController::class, 'show']);
        Route::patch('/settings', [SettingsController::class, 'update']);
        Route::post('/settings/password', [SettingsController::class, 'changePassword']);
        Route::post('/settings/photo', [SettingsController::class, 'updatePhoto']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/stream', [NotificationController::class, 'stream']);
        Route::patch('/notifications/{id}', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/preferences', [NotificationController::class, 'updatePreferences']);

        // Tiers
        Route::get('/tiers', [TierController::class, 'index']);
        Route::get('/tiers/my', [TierController::class, 'myTier']);
        Route::post('/tiers/upgrade', [TierController::class, 'upgrade']);

        // Referrals
        Route::get('/referrals', [ReferralController::class, 'index']);
        Route::post('/referrals/code', [ReferralController::class, 'generateCode']);

        // Support
        Route::get('/support', [SupportController::class, 'index']);
        Route::post('/support', [SupportController::class, 'store']);
        Route::get('/support/{id}', [SupportController::class, 'show']);
        Route::post('/support/{id}/message', [SupportController::class, 'addMessage']);
        Route::post('/support/{id}/close', [SupportController::class, 'close']);

        // AI & Voice
        Route::post('/ai/chat', [AIAssistantController::class, 'chat']);
        Route::post('/voice/process', [AIAssistantController::class, 'processVoice']);
        Route::get('/voice/voices', [AIAssistantController::class, 'voices']);

        // Analytics
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('/analytics/performance', [AnalyticsController::class, 'performance']);

        // KYC-Gated Financial Routes
        Route::middleware('kyc.verified')->group(function () {
            // Fiat
            Route::get('/fiat/accounts', [FiatAccountController::class, 'index']);
            Route::post('/fiat/accounts/virtual', [FiatAccountController::class, 'createVirtualAccount']);
            Route::post('/fiat/deposit', [FiatAccountController::class, 'deposit']);
            Route::post('/fiat/withdraw', [FiatAccountController::class, 'withdraw']);
            Route::post('/fiat/transfer', [FiatAccountController::class, 'transfer']);
            Route::get('/fiat/banks', [FiatAccountController::class, 'banks']);
            Route::post('/fiat/banks/resolve', [FiatAccountController::class, 'resolveBankAccount']);
            Route::get('/fiat/transactions', [FiatAccountController::class, 'transactions']);

            // Crypto
            Route::get('/crypto/wallets', [WalletController::class, 'index']);
            Route::post('/crypto/wallets', [WalletController::class, 'store']);
            Route::post('/crypto/wallets/import', [WalletController::class, 'importWallet']);
            Route::post('/crypto/buy', [CryptoTransactionController::class, 'buy']);
            Route::post('/crypto/sell', [CryptoTransactionController::class, 'sell']);
            Route::post('/crypto/send', [CryptoTransactionController::class, 'send']);
            Route::get('/crypto/swap/rate', [SwapController::class, 'getRate']);
            Route::post('/crypto/swap', [SwapController::class, 'execute']);
            Route::get('/market/prices', [MarketController::class, 'prices']);
            Route::get('/market/ohlcv', [MarketController::class, 'ohlcv']);
            Route::get('/market/prices/stream', [MarketController::class, 'priceStream']);
            Route::get('/market/{coinId}', [MarketController::class, 'coinDetail']);

            // DCA (Recurring Purchases)
            Route::get('/crypto/dca', [RecurringPurchaseController::class, 'index']);
            Route::post('/crypto/dca', [RecurringPurchaseController::class, 'store']);
            Route::delete('/crypto/dca/{id}', [RecurringPurchaseController::class, 'destroy']);

            // Price Alerts
            Route::get('/crypto/alerts', [PriceAlertController::class, 'index']);
            Route::post('/crypto/alerts', [PriceAlertController::class, 'store']);
            Route::delete('/crypto/alerts/{id}', [PriceAlertController::class, 'destroy']);

            // Staking
            Route::get('/crypto/staking', [StakingController::class, 'index']);
            Route::post('/crypto/staking', [StakingController::class, 'stake']);
            Route::post('/crypto/staking/{id}/unstake', [StakingController::class, 'unstake']);

            // Multi-Sig Wallets
            Route::get('/crypto/multisig', [MultiSigController::class, 'index']);
            Route::post('/crypto/multisig', [MultiSigController::class, 'store']);
            Route::post('/crypto/multisig/{walletId}/propose', [MultiSigController::class, 'proposeTransaction']);

            // Trading
            Route::post('/trading/binary', [BinaryTradingController::class, 'execute']);
            Route::get('/trading/binary/{id}', [BinaryTradingController::class, 'show']);
            Route::get('/trading/binary-history', [BinaryTradingController::class, 'history']);
            Route::get('/trading/price/{symbol}', [BinaryTradingController::class, 'currentPrice']);
            Route::get('/trading/p2p', [P2PController::class, 'index']);
            Route::post('/trading/p2p', [P2PController::class, 'store']);
            Route::post('/trading/p2p/{offerId}/confirm', [P2PController::class, 'confirm']);
            Route::post('/trading/p2p/{offerId}/dispute', [P2PController::class, 'dispute']);
            Route::delete('/trading/p2p/{offerId}', [P2PController::class, 'destroy']);

            // Virtual Cards
            Route::get('/cards', [VirtualCardController::class, 'index']);
            Route::post('/cards', [VirtualCardController::class, 'store']);
            Route::patch('/cards/{cardId}', [VirtualCardController::class, 'update']);

            // Savings
            Route::get('/savings', [SavingsController::class, 'index']);
            Route::post('/savings', [SavingsController::class, 'store']);
            Route::post('/savings/{id}/withdraw-early', [SavingsController::class, 'withdrawEarly']);

            // Investments
            Route::get('/investments', [InvestmentController::class, 'index']);
            Route::post('/investments', [InvestmentController::class, 'store']);

            // Loans
            Route::get('/loans', [LoanController::class, 'index']);
            Route::post('/loans', [LoanController::class, 'store']);
            Route::post('/loans/{id}/repay', [LoanController::class, 'repay']);

            // Receipts & Statements
            Route::get('/receipts', [ReceiptController::class, 'index']);
            Route::post('/receipts/{transactionId}', [ReceiptController::class, 'generate']);
            Route::post('/statements', [ReceiptController::class, 'generateStatement']);

            // Scheduled Transfers
            Route::get('/fiat/scheduled', [ScheduledTransferController::class, 'index']);
            Route::post('/fiat/scheduled', [ScheduledTransferController::class, 'store']);
            Route::delete('/fiat/scheduled/{id}', [ScheduledTransferController::class, 'destroy']);
        });
    });

    // Webhooks - No auth, signature verified
    Route::prefix('webhooks')->group(function () {
        Route::post('/korapay', [KorapayWebhookController::class, 'handle']);
        Route::post('/paystack', [PaystackWebhookController::class, 'handle']);
        Route::post('/flutterwave', [FlutterwaveWebhookController::class, 'handle']);
        Route::post('/paypal', [PayPalWebhookController::class, 'handle']);
    });
});

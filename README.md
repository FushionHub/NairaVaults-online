# NairaVault

Nigeria's premier fintech platform for fiat banking, crypto trading, and wealth management.

## Technology Stack

- **Backend**: Laravel 11 (PHP 8.3+), MySQL 8 / PostgreSQL 16
- **Frontend**: Next.js 14, React 18, TypeScript, Tailwind CSS, shadcn/ui, Zustand
- **Authentication**: Laravel Sanctum, Google OAuth (Socialite), TOTP 2FA
- **Crypto**: Privy.io (embedded wallets), Binance API, CoinGecko API
- **Payments**: Korapay, Paystack, Flutterwave, PayPal
- **AI**: Google Gemini, xAI Grok, ElevenLabs TTS
- **Real-time**: Laravel Reverb (WebSockets), Server-Sent Events
- **Testing**: Pest PHP (backend), Vitest (frontend)

## Features

- **Multi-Currency Banking** — NGN, USD, EUR, GBP, GHS accounts with virtual account numbers
- **Crypto Trading** — Buy, sell, swap 50+ cryptocurrencies with live Binance prices
- **Binary Trading** — Predict market movements with real-time charts
- **P2P Trading** — Peer-to-peer crypto exchange with escrow
- **Virtual Cards** — Visa, Mastercard, Verve for online payments
- **Savings & Investments** — Lock funds and earn competitive interest
- **Loans** — Apply for loans with automated repayment schedules
- **DCA (Dollar Cost Averaging)** — Automated recurring crypto purchases
- **Staking** — Stake crypto and earn APY rewards
- **Multi-Signature Wallets** — Business-grade shared wallets
- **Price Alerts** — Get notified when prices hit targets
- **AI Financial Assistant** — Powered by Gemini & Grok
- **Voice Assistant** — ElevenLabs natural speech synthesis
- **KYC Verification** — BVN/NIN via Dojah, business verification
- **Referral Program** — Tiered referral rewards
- **Scheduled Transfers** — Automate recurring payments
- **Analytics Dashboard** — Portfolio performance tracking
- **Receipts & Statements** — PDF generation with dompdf

## Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 18+
- MySQL 8 or PostgreSQL 16
- Redis 7+

## Setup

### Backend

```bash
# Clone and install
cd /path/to/NairaVault
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nairavault
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seed
php artisan migrate
php artisan db:seed

# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Start server
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### Environment Variables

Copy `.env.example` and configure all API keys:

- **Database**: MySQL/PostgreSQL credentials
- **Payments**: Korapay, Paystack, Flutterwave, PayPal API keys
- **Crypto**: Binance API key/secret, CoinGecko API key, Privy app ID
- **KYC**: Dojah API key
- **AI**: Gemini API key, Grok API key, ElevenLabs API key
- **Auth**: Google OAuth client ID/secret
- **SMS**: Termii API key
- **Notifications**: Firebase credentials

## API Documentation

All API endpoints are prefixed with `/api/v1/`.

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Register new user |
| POST | `/auth/login` | Login |
| POST | `/auth/2fa/verify` | Verify 2FA code |
| POST | `/auth/logout` | Logout (auth required) |
| POST | `/auth/forgot-password` | Request password reset |

### Fiat Banking (KYC required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/fiat/accounts` | List fiat accounts |
| POST | `/fiat/deposit` | Initiate deposit |
| POST | `/fiat/withdraw` | Withdraw to bank |
| POST | `/fiat/transfer` | Internal transfer |

### Crypto (KYC required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/crypto/wallets` | List wallets |
| POST | `/crypto/buy` | Buy crypto |
| POST | `/crypto/sell` | Sell crypto |
| POST | `/crypto/swap` | Swap between coins |
| POST | `/crypto/send` | Send to address |

### Trading (KYC required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/trading/binary` | Execute binary trade |
| GET | `/trading/p2p` | List P2P offers |
| POST | `/trading/p2p` | Create P2P offer |

## Security

- All monetary amounts use `DECIMAL(20,8)` and BCMath arithmetic
- PII (BVN, NIN, private keys) encrypted at rest with AES-256
- Webhook signatures verified with HMAC before processing
- 60-second withdrawal deduplication via Redis
- Private keys never returned in API responses
- Rate limiting on sensitive endpoints
- KYC gating on all financial routes
- 2FA with TOTP, encrypted backup codes, 15-minute lockout

## License

Proprietary. All rights reserved.

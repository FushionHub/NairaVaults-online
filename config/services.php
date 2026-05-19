<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'dojah' => [
        'app_id' => env('DOJAH_APP_ID'),
        'private_key' => env('DOJAH_PRIVATE_KEY'),
        'base_url' => env('DOJAH_BASE_URL', 'https://api.dojah.io'),
    ],

    'privy' => [
        'app_id' => env('PRIVY_APP_ID'),
        'app_secret' => env('PRIVY_APP_SECRET'),
        'base_url' => env('PRIVY_BASE_URL', 'https://auth.privy.io'),
    ],

    'binance' => [
        'api_key' => env('BINANCE_API_KEY'),
        'secret_key' => env('BINANCE_SECRET_KEY'),
        'base_url' => env('BINANCE_BASE_URL', 'https://api.binance.com'),
        'ws_url' => env('BINANCE_WS_URL', 'wss://stream.binance.com:9443'),
    ],

    'coingecko' => [
        'api_key' => env('COINGECKO_API_KEY'),
        'base_url' => env('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3'),
    ],

    'korapay' => [
        'secret_key' => env('KORAPAY_SECRET_KEY'),
        'public_key' => env('KORAPAY_PUBLIC_KEY'),
        'encryption_key' => env('KORAPAY_ENCRYPTION_KEY'),
        'base_url' => env('KORAPAY_BASE_URL', 'https://api.korapay.com'),
        'webhook_secret' => env('KORAPAY_WEBHOOK_SECRET'),
    ],

    'paystack' => [
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    ],

    'flutterwave' => [
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'base_url' => env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com/v3'),
        'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'base_url' => env('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

    'grok' => [
        'api_key' => env('GROK_API_KEY'),
        'base_url' => env('GROK_BASE_URL', 'https://api.x.ai/v1'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID'),
        'base_url' => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io/v1'),
    ],

    'termii' => [
        'api_key' => env('TERMII_API_KEY'),
        'base_url' => env('TERMII_BASE_URL', 'https://api.ng.termii.com'),
    ],

    'vtpass' => [
        'api_key' => env('VTPASS_API_KEY'),
        'public_key' => env('VTPASS_PUBLIC_KEY'),
        'secret_key' => env('VTPASS_SECRET_KEY'),
        'base_url' => env('VTPASS_BASE_URL', 'https://vtpass.com/api'),
    ],

    'cardtonic' => [
        'api_key' => env('CARDTONIC_API_KEY'),
        'base_url' => env('CARDTONIC_BASE_URL', 'https://api.cardtonic.com'),
    ],

    'firebase' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'sender_id' => env('FIREBASE_SENDER_ID'),
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'trongrid' => [
        'api_key' => env('TRON_API_KEY', ''),
        'base_url' => env('TRON_BASE_URL', 'https://api.trongrid.io'),
    ],

    'tronscan' => [
        'api_key' => env('TRON_API_KEY', ''),
        'base_url' => env('TRONSCAN_BASE_URL', 'https://apilist.tronscanapi.com/api'),
    ],

    'etherscan' => [
        'api_key' => env('ETHERSCAN_API_KEY', 'TCMFF1ZWPKXQ6V4PETA2FQRCBGCC2WT686'),
        'base_url' => env('ETHERSCAN_BASE_URL', 'https://api.etherscan.io/v2/api'),
    ],

    'bscscan' => [
        'api_key' => env('BSCSCAN_API_KEY', 'TCMFF1ZWPKXQ6V4PETA2FQRCBGCC2WT686'),
        'base_url' => env('BSCSCAN_BASE_URL', 'https://api.etherscan.io/v2/api'),
        'chainid' => 56,
    ],

    'solana' => [
        'api_key' => env('HELIUS_API_KEY', 'af45c17e-02d2-4231-838f-d4e1e002477c'),
        'rpc_url' => env('SOLANA_RPC_URL', 'https://mainnet.helius-rpc.com/?api-key=af45c17e-02d2-4231-838f-d4e1e002477c'),
    ],



];

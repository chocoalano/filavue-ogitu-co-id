<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'telegram' => [
        'bug_report_chat_id' => env('TELEGRAM_BUG_REPORT_CHAT_ID'),
    ],

    'midtrans' => [
        'env' => (function (): string {
            $configuredEnv = strtolower(trim((string) env('MIDTRANS_ENV', '')));

            if (in_array($configuredEnv, ['sandbox', 'production'], true)) {
                return $configuredEnv;
            }

            return env('MIDTRANS_IS_PRODUCTION', false) ? 'production' : 'sandbox';
        })(),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'iris_key' => env('MIDTRANS_IRIS_KEY'),
    ],

    'lion_parcel' => [
        'base_url' => env('LION_PARCEL_BASE_URL', 'https://api-middleware.lionparcel.com'),
        'auth' => env('LION_PARCEL_AUTH', 'WkVOSVRIIFNJTkVSR0kgVVRBTUEgQjJCIENPUlAgQ0dLIENMMzgyMTM='),
        'origin' => env('LION_PARCEL_ORIGIN', 'KALIDERES, JAKARTA BARAT'),
        'commodity' => env('LION_PARCEL_COMMODITY', 'ABR036'),
    ],

    // 'qontak' => [
    //     'api_token' => env('QONTAK_API_TOKEN'),
    //     'channel_integration_id' => env('QONTAK_CHANNEL_INTEGRATION_ID'),
    //     'wd_approved_template_id' => env('QONTAK_WD_APPROVED_TEMPLATE_ID'),
    //     'wd_approved_header_image_url' => env('QONTAK_WD_APPROVED_HEADER_IMAGE_URL'),
    //     'wd_rejected_template_id' => env('QONTAK_WD_REJECTED_TEMPLATE_ID'),
    //     'broadcast_template_id' => env('QONTAK_BROADCAST_TEMPLATE_ID'),
    //     'broadcast_header_image_url' => env('QONTAK_BROADCAST_HEADER_IMAGE_URL'),
    //     'wa_gateway_number' => env('QONTAK_WA_GATEWAY_NUMBER'),
    //     'wa_gateway_hemat_mode' => env('QONTAK_WA_GATEWAY_HEMAT_MODE', false),
    // ],
    'qontak' => [
        'base_url' => env('QONTAK_BASE_URL') ?: 'https://service-chat.qontak.com/api/open/v1',
        'api_token' => env('QONTAK_API_TOKEN'),
        'channel_integration_id' => env('QONTAK_CHANNEL_INTEGRATION_ID'),
        'timeout' => env('QONTAK_TIMEOUT', 30),
        'bulk_retry_attempts' => env('QONTAK_BULK_RETRY_ATTEMPTS', 2),
        'bulk_retry_buffer_seconds' => env('QONTAK_BULK_RETRY_BUFFER_SECONDS', 2),

        'wa_gateway_number' => env('QONTAK_WA_GATEWAY_NUMBER'),
        'wa_gateway_hemat_mode' => env('QONTAK_WA_GATEWAY_HEMAT_MODE', false),
        'password_reset_template_id' => env('QONTAK_PASSWORD_RESET_TEMPLATE_ID'),

        'wd_approved_enabled' => env('QONTAK_WD_APPROVED_ENABLED', true),
        'wd_approved_template_id' => env('QONTAK_WD_APPROVED_TEMPLATE_ID'),
        'wd_approved_header_image_url' => env('QONTAK_WD_APPROVED_HEADER_IMAGE_URL'),
        'wd_rejected_enabled' => env('QONTAK_WD_REJECTED_ENABLED', true),
        'wd_rejected_template_id' => env('QONTAK_WD_REJECTED_TEMPLATE_ID'),
        'broadcast_template_id' => env('QONTAK_BROADCAST_TEMPLATE_ID'),
        'broadcast_header_image_url' => env('QONTAK_BROADCAST_HEADER_IMAGE_URL'),
    ],

    'rajaongkir' => [
        'api_key_shipping' => env('RAJAONGKIR_API_KEY_SHIPPING'),
        'api_key_delivery' => env('RAJAONGKIR_API_KEY_DELIVERY'),
        'origin_district_id' => env('RAJAONGKIR_ORIGIN_DISTRICT_ID', 135),
    ],

];

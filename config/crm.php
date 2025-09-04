<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CRM Read Access
    |--------------------------------------------------------------------------
    |
    | This option controls whether the CRM API allows read operations.
    | When disabled, all GET endpoints will return 503 Service Unavailable.
    |
    */
    'read_enabled'                => env('CRM_READ_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | CRM Write Access
    |--------------------------------------------------------------------------
    |
    | This option controls whether the CRM API allows write operations.
    | When disabled, POST, PUT, PATCH, DELETE endpoints will return 503.
    |
    */
    'write_enabled'               => env('CRM_WRITE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | When APP_DEMO is true, the application runs in demo mode with:
    | - Isolated SQLite databases per session/token
    | - Automatic cleanup of expired demo data
    | - Rate limiting for demo usage
    | - Disabled side effects (mail, queues, webhooks)
    |
    */
    'demo_mode'                   => env('APP_DEMO', false),

    /*
    |--------------------------------------------------------------------------
    | Demo Database Template
    |--------------------------------------------------------------------------
    |
    | Path to the template SQLite database used for demo mode.
    | This database is copied for each demo session/token.
    |
    */
    'demo_template_path'          => storage_path('app/demo/template.sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Demo Session TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live for demo sessions in minutes.
    | After this time, demo databases and uploads are eligible for cleanup.
    |
    */
    'demo_session_ttl'            => env('DEMO_SESSION_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Demo Token TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live for demo API tokens in minutes.
    |
    */
    'demo_token_ttl'              => env('DEMO_TOKEN_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Public Demo Mode (Legacy)
    |--------------------------------------------------------------------------
    |
    | When enabled, this puts the API in demo mode for public testing.
    | In demo mode, write operations may be restricted or logged differently.
    | This is kept for backward compatibility.
    |
    */
    'public_demo_mode'            => env('CRM_PUBLIC_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode Message
    |--------------------------------------------------------------------------
    |
    | Message to display when write operations are disabled.
    |
    */
    'disabled_message'            => 'This service is temporarily unavailable for maintenance.',

    /*
    |--------------------------------------------------------------------------
    | Demo Mode Write Message
    |--------------------------------------------------------------------------
    |
    | Message to display when write operations are disabled in demo mode.
    |
    */
    'demo_write_disabled_message' => 'Write operations are disabled in demo mode. This is a read-only demonstration.',
];

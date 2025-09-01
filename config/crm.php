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
    'read_enabled' => env('CRM_READ_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | CRM Write Access
    |--------------------------------------------------------------------------
    |
    | This option controls whether the CRM API allows write operations.
    | When disabled, POST, PUT, PATCH, DELETE endpoints will return 503.
    |
    */
    'write_enabled' => env('CRM_WRITE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Public Demo Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, this puts the API in demo mode for public testing.
    | In demo mode, write operations may be restricted or logged differently.
    |
    */
    'public_demo_mode' => env('CRM_PUBLIC_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode Message
    |--------------------------------------------------------------------------
    |
    | Message to display when write operations are disabled.
    |
    */
    'disabled_message' => 'This service is temporarily unavailable for maintenance.',

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
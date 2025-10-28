<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable SMS Catcher
    |--------------------------------------------------------------------------
    |
    | By default the sms catcher is only active when the application is running
    | in the local environment or when APP_DEBUG is true. You can explicitly
    | enable or disable it via the SMS_CATCHER_ENABLED environment variable.
    */

    'enabled' => env('SMS_CATCHER_ENABLED', env('APP_ENV') === 'local' || env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Route configuration
    |--------------------------------------------------------------------------
    |
    | Customize the prefix and middleware that wrap the sms catcher dashboard.
    | The dashboard is intentionally registered inside the web middleware group
    | so sessions and CSRF protection are available.
    */

    'route' => [
        'prefix' => 'sms-catcher',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage file
    |--------------------------------------------------------------------------
    |
    | Messages are persisted to a lightweight JSON file inside the application's
    | storage directory. You may change the location if required.
    */

    'storage_path' => storage_path('framework/sms-catcher.json'),
];

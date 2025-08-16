<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Master/System admin guard
        'master' => [
            'driver' => 'session',
            'provider' => 'system_users',
        ],

        // Client/Tenant guard (will be configured dynamically)
        'client' => [
            'driver' => 'session',
            'provider' => 'client_users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // System users provider (master database)
        'system_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Master\SystemUser::class,
        ],

        // Client users provider (client database)
        'client_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Client\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'system_users' => [
            'provider' => 'system_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'client_users' => [
            'provider' => 'client_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'master'),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // Default PostgreSQL connection (points to master for compatibility)
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_MASTER_HOST', '127.0.0.1'),
            'port' => env('DB_MASTER_PORT', '5432'),
            'database' => env('DB_MASTER_DATABASE', 'pos_system_master'),
            'username' => env('DB_MASTER_USERNAME', 'postgres'),
            'password' => env('DB_MASTER_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        // Master database connection (for system users and companies)
        'master' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_MASTER_HOST', '127.0.0.1'),
            'port' => env('DB_MASTER_PORT', '5432'),
            'database' => env('DB_MASTER_DATABASE', 'pos_system_master'),
            'username' => env('DB_MASTER_USERNAME', 'postgres'),
            'password' => env('DB_MASTER_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        // Client database connection template (will be dynamically configured)
        'client_template' => [
            'driver' => 'pgsql',
            'host' => env('DB_CLIENT_HOST', '127.0.0.1'),
            'port' => env('DB_CLIENT_PORT', '5432'),
            'database' => '', // Will be set dynamically
            'username' => env('DB_CLIENT_USERNAME', 'postgres'),
            'password' => env('DB_CLIENT_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        // Dynamic client connection (will be configured at runtime)
        'client' => [
            'driver' => 'pgsql',
            'host' => env('DB_CLIENT_HOST', '127.0.0.1'),
            'port' => env('DB_CLIENT_PORT', '5432'),
            'database' => '', // Will be set dynamically
            'username' => env('DB_CLIENT_USERNAME', 'postgres'),
            'password' => env('DB_CLIENT_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
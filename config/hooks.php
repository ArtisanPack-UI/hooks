<?php

declare(strict_types=1);
/**
 * Hooks package configuration.
 *
 * Reading env values through this config file (rather than getenv() at call
 * time) means the deprecation log level survives `php artisan config:cache`
 * and works on the modern Laravel default of Env::disablePutenv().
 *
 * @since 1.3.0
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Deprecation Log Level
    |--------------------------------------------------------------------------
    |
    | Level at which alias resolutions are logged the first time each unique
    | old-name is seen. Accepts any PSR-3 level ("emergency", "alert",
    | "critical", "error", "warning", "notice", "info", "debug") or "off" to
    | suppress the log entirely.
    |
    */

    'deprecation_level' => env('HOOKS_DEPRECATION_LEVEL', 'info'),

];

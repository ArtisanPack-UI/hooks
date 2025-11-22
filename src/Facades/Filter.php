<?php

declare(strict_types=1);
/**
 * Filter Facade
 *
 * Provides a static-like interface to the Filter service.
 *
 * @since      1.0.0
 *
 * @method static void add(string $hook, callable $callback, int $priority = 10)
 * @method static mixed apply(string $hook, mixed $value, ...$args)
 * @method static bool remove(string $hook, callable $callback, int $priority = 10 )
 * @method static bool removeAll( string $hook, int|false $priority = false )
 */

namespace ArtisanPackUI\Hooks\Facades;

use ArtisanPackUI\Hooks\Filter as FilterManager;
use Illuminate\Support\Facades\Facade;

/**
 * Provides static access to the Filter manager.
 *
 * @since 1.0.0
 */
class Filter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @since 1.0.0
     */
    protected static function getFacadeAccessor(): string
    {
        return FilterManager::class;
    }
}

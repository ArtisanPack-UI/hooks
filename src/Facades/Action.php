<?php

declare(strict_types=1);
/**
 * Action Facade
 *
 * Provides a static-like interface to the Action service.
 *
 * @since      1.0.0
 *
 * @method static void add(string $hook, callable $callback, int $priority = 10)
 * @method static void do(string $hook, ...$args)
 * @method static bool remove(string $hook, callable $callback, int $priority = 10 )
 * @method static bool removeAll( string $hook, int|false $priority = false )
 */

namespace ArtisanPackUI\Hooks\Facades;

use ArtisanPackUI\Hooks\Action as ActionManager;
use Illuminate\Support\Facades\Facade;

/**
 * Provides static access to the Action manager.
 *
 * @since 1.0.0
 */
class Action extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @since 1.0.0
     */
    protected static function getFacadeAccessor(): string
    {
        return ActionManager::class;
    }
}

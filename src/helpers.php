<?php

declare(strict_types=1);
/**
 * Helper functions for the Hooks package.
 *
 * Provides easy access to the Action and Filter managers, using a
 * camelCase naming convention for modern PHP development.
 *
 * @since      1.0.0
 */

use ArtisanPackUI\Hooks\Facades\Action;
use ArtisanPackUI\Hooks\Facades\Filter;
use ArtisanPackUI\Hooks\HookDeprecations;

if (! function_exists('addAction')) {
    /**
     * Adds a callback to a specific action hook.
     *
     * @since 1.0.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Action::add()
     *
     * @param  string  $hook  The name of the action.
     * @param  callable  $callback  The callback to be executed.
     * @param  int  $priority  Optional. The priority of the callback. Default 10.
     */
    function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        Action::add($hook, $callback, $priority);
    }
}

if (! function_exists('doAction')) {
    /**
     * Executes all registered callbacks for a given action.
     *
     * @since 1.0.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Action::do()
     *
     * @param  string  $hook  The name of the action to execute.
     * @param  mixed  ...$args  Optional. The arguments to pass to the callbacks.
     */
    function doAction(string $hook, mixed ...$args): void
    {
        Action::do($hook, ...$args);
    }
}

if (! function_exists('removeAction')) {
    /**
     * Removes a specific callback from an action hook.
     *
     * @since 1.1.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Action::remove()
     *
     * @param  string  $hook  The name of the action.
     * @param  callable  $callback  The specific callback to remove.
     * @param  int  $priority  Optional. The priority of the callback. Default 10.
     *
     * @return bool True on success, false on failure.
     */
    function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        return Action::remove($hook, $callback, $priority);
    }
}

if (! function_exists('removeAllActions')) {
    /**
     * Removes all callbacks for a specific action hook or a specific priority.
     *
     * @since 1.1.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Action::removeAll()
     *
     * @param  string  $hook  The name of the action.
     * @param  false|int  $priority  Optional. A specific priority to remove. If false, all priorities are removed. Default false.
     *
     * @return bool True if callbacks were removed, false otherwise.
     */
    function removeAllActions(string $hook, int|false $priority = false): bool
    {
        return Action::removeAll($hook, $priority);
    }
}

if (! function_exists('addFilter')) {
    /**
     * Adds a callback to a specific filter hook.
     *
     * @since 1.0.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Filter::add()
     *
     * @param  string  $hook  The name of the filter.
     * @param  callable  $callback  The callback to be executed.
     * @param  int  $priority  Optional. The priority of the callback. Default 10.
     */
    function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        Filter::add($hook, $callback, $priority);
    }
}

if (! function_exists('applyFilters')) {
    /**
     * Applies all registered callbacks to a filter.
     *
     * @since 1.0.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Filter::apply()
     *
     * @param  string  $hook  The name of the filter to apply.
     * @param  mixed  $value  The initial value to be filtered.
     * @param  mixed  ...$args  Optional. Additional arguments to pass to the callbacks.
     *
     * @return mixed The filtered value.
     */
    function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return Filter::apply($hook, $value, ...$args);
    }
}

if (! function_exists('removeFilter')) {
    /**
     * Removes a specific callback from a filter hook.
     *
     * @since 1.1.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Filter::remove()
     *
     * @param  string  $hook  The name of the filter.
     * @param  callable  $callback  The specific callback to remove.
     * @param  int  $priority  Optional. The priority of the callback. Default 10.
     *
     * @return bool True on success, false on failure.
     */
    function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        return Filter::remove($hook, $callback, $priority);
    }
}

if (! function_exists('removeAllFilters')) {
    /**
     * Removes all callbacks for a specific filter hook or a specific priority.
     *
     * @since 1.1.0
     *
     * @uses \ArtisanPackUI\Hooks\Facades\Filter::removeAll()
     *
     * @param  string  $hook  The name of the filter.
     * @param  false|int  $priority  Optional. A specific priority to remove. If false, all priorities are removed. Default false.
     *
     * @return bool True if callbacks were removed, false otherwise.
     */
    function removeAllFilters(string $hook, int|false $priority = false): bool
    {
        return Filter::removeAll($hook, $priority);
    }
}

if (! function_exists('deprecateHook')) {
    /**
     * Register a hook rename so old-name subscribers continue to fire on
     * the canonical hook.
     *
     * A deprecation notice is logged the first time an alias is resolved
     * this process. Logging is env-gated via HOOKS_DEPRECATION_LEVEL
     * (warning|info|debug|off, default info).
     *
     * @since 1.3.0
     *
     * @uses \ArtisanPackUI\Hooks\HookDeprecations::alias()
     *
     * @param  string  $old  The previous hook name.
     * @param  string  $new  The canonical hook name.
     */
    function deprecateHook(string $old, string $new): void
    {
        app(HookDeprecations::class)->alias($old, $new);
    }
}

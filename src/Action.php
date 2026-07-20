<?php

declare(strict_types=1);
/**
 * Action Hooks Manager
 *
 * Manages the registration and execution of action hooks, leveraging
 * Laravel's service container for dependency injection in callbacks.
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks;

use ArtisanPackUI\Hooks\Concerns\ManagesHookBuckets;
use Illuminate\Contracts\Container\Container;

/**
 * Manages the registration and execution of action hooks.
 *
 * Storage, alias resolution, add/remove/removeAll and the dedup-across-
 * buckets logic live in {@see ManagesHookBuckets} so Action and Filter
 * cannot drift on shared bookkeeping.
 *
 * @since 1.0.0
 */
class Action
{
    use ManagesHookBuckets;

    /**
     * The application container instance.
     *
     * @since 1.0.0
     */
    protected Container $app;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param  Container  $app  The application container.
     * @param  HookDeprecations|null  $deprecations  Optional deprecations manager.
     */
    public function __construct(Container $app, ?HookDeprecations $deprecations = null)
    {
        $this->app          = $app;
        $this->deprecations = $deprecations;
    }

    /**
     * Executes all registered callbacks for a given action.
     *
     * @since 1.0.0
     *
     * @param  string  $hook  The name of the action to execute.
     * @param  mixed  ...$args  Optional. The arguments to pass to the callbacks.
     */
    public function do(string $hook, mixed ...$args): void
    {
        foreach ($this->collectForDispatch($hook) as $callback) {
            $callback(...$args);
        }
    }
}

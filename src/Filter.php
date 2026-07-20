<?php

declare(strict_types=1);
/**
 * Filter Hooks Manager
 *
 * Manages the registration and execution of filter hooks.
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks;

use ArtisanPackUI\Hooks\Concerns\ManagesHookBuckets;
use Illuminate\Contracts\Container\Container;

/**
 * Manages the registration and execution of filter hooks.
 *
 * Storage, alias resolution, add/remove/removeAll and the dedup-across-
 * buckets logic live in {@see ManagesHookBuckets}.
 *
 * @since 1.0.0
 */
class Filter
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
     * Applies all registered callbacks to a filter.
     *
     * @since 1.0.0
     *
     * @param  string  $hook  The name of the filter to apply.
     * @param  mixed  $value  The initial value to be filtered.
     * @param  mixed  ...$args  Optional. Additional arguments to pass to the callbacks.
     *
     * @return mixed The filtered value.
     */
    public function apply(string $hook, mixed $value, mixed ...$args): mixed
    {
        $callbacks = $this->collectForDispatch($hook);

        if (empty($callbacks)) {
            return $value;
        }

        $allArgs = array_merge([$value], $args);

        foreach ($callbacks as $callback) {
            $value      = $callback(...$allArgs);
            $allArgs[0] = $value;
        }

        return $value;
    }
}

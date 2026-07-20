<?php

declare(strict_types=1);
/**
 * Hooks Service Provider
 *
 * Registers the Action, Filter, and HookDeprecations singletons and wires
 * the deprecation-log reset into Octane / queue lifecycle events.
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Providers;

use ArtisanPackUI\Hooks\Action;
use ArtisanPackUI\Hooks\Filter;
use ArtisanPackUI\Hooks\HookDeprecations;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider that registers the package bindings.
 *
 * @since 1.0.0
 */
class HooksServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/hooks.php', 'artisanpack.hooks');

        // Register HookDeprecations as a singleton so alias state is
        // shared across every Action/Filter resolution.
        $this->app->singleton(HookDeprecations::class, function () {
            return new HookDeprecations;
        });

        // Register Action as a singleton.
        $this->app->singleton(Action::class, function ($app) {
            return new Action($app, $app->make(HookDeprecations::class));
        });

        // Register Filter as a singleton.
        $this->app->singleton(Filter::class, function ($app) {
            return new Filter($app, $app->make(HookDeprecations::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @since 1.0.0
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/hooks.php' => $this->app->configPath('artisanpack/hooks.php'),
        ], 'hooks-config');

        $this->registerLogResetListeners();
    }

    /**
     * Reset the deprecation log-once budget on every request/job boundary.
     *
     * Without this, HookDeprecations::$logged would accumulate across an
     * Octane worker's lifetime and a deprecated hook fired thousands of
     * times a second would log exactly once at worker boot — operators
     * would then miss it and assume the migration is complete.
     *
     * Uses string event names so the package does not have to depend on
     * either laravel/octane or laravel/queue being installed; the
     * dispatcher simply never fires them when the classes are absent.
     *
     * @since 1.3.0
     */
    protected function registerLogResetListeners(): void
    {
        if (! $this->app->bound(Dispatcher::class)) {
            return;
        }

        $events = $this->app->make(Dispatcher::class);

        $reset = function (): void {
            $this->app->make(HookDeprecations::class)->resetLogState();
        };

        // Octane: reset before each request the worker serves.
        $events->listen('Laravel\\Octane\\Events\\RequestReceived', $reset);

        // Queue worker: reset before each job the worker picks up.
        $events->listen('Illuminate\\Queue\\Events\\JobProcessing', $reset);
    }
}

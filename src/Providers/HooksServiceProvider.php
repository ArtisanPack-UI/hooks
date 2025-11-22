<?php

declare(strict_types=1);
/**
 * Hooks Service Provider
 *
 * Registers the Action and Filter singletons for the Hooks package.
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Providers;

use ArtisanPackUI\Hooks\Action;
use ArtisanPackUI\Hooks\Filter;
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
     * Binds the Action and Filter managers into the container as singletons.
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        // Register Action as a singleton.
        $this->app->singleton(Action::class, function ($app) {
            return new Action($app);
        });

        // Register Filter as a singleton.
        $this->app->singleton(Filter::class, function ($app) {
            return new Filter($app);
        });
    }
}

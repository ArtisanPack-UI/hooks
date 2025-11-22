<?php

declare(strict_types=1);
/**
 * Blade Directive Service Provider
 *
 * Registers the custom Blade directives (@action and @filter) for the Hooks package.
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Registers custom Blade directives.
 *
 * @since 1.0.0
 */
class BladeDirectiveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @since 1.0.0
     */
    public function boot(): void
    {
        // Blade directive for actions: @action('hook_name', $arg1, ...)
        Blade::directive('action', function ($expression) {
            return "<?php doAction({$expression}); ?>";
        });

        // Blade directive for filters: @filter('hook_name', $value, ...)
        Blade::directive('filter', function ($expression) {
            return "<?php echo applyFilters({$expression}); ?>";
        });
    }
}

<?php
/**
 * Blade Directive Service Provider
 *
 * Registers the custom Blade directives (@action and @filter) for the Hooks package.
 *
 * @package    ArtisanPackUI\Hooks
 * @subpackage ArtisanPackUI\Hooks\Providers
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
	 * @return void
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
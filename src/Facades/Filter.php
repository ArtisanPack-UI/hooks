<?php
/**
 * Filter Facade
 *
 * Provides a static-like interface to the Filter service.
 *
 * @package    ArtisanPackUI\Hooks
 * @subpackage ArtisanPackUI\Hooks\Facades
 * @since      1.0.0
 *
 * @method static void add(string $hook, callable $callback, int $priority = 10)
 * @method static mixed apply(string $hook, mixed $value, ...$args)
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
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor(): string
	{
		return FilterManager::class;
	}
}
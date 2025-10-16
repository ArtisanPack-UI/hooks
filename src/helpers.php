<?php
/**
 * Helper functions for the Hooks package.
 *
 * Provides easy access to the Action and Filter managers, using a
 * camelCase naming convention for modern PHP development.
 *
 * @package    ArtisanPackUI\Hooks
 * @since      1.0.0
 */

use ArtisanPackUI\Hooks\Facades\Action;
use ArtisanPackUI\Hooks\Facades\Filter;

if (! function_exists('addAction')) {
	/**
	 * Adds a callback to a specific action hook.
	 *
	 * @since 1.0.0
	 * @uses \ArtisanPackUI\Hooks\Facades\Action::add()
	 *
	 * @param string   $hook     The name of the action.
	 * @param callable $callback The callback to be executed.
	 * @param int      $priority Optional. The priority of the callback. Default 10.
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
	 * @uses \ArtisanPackUI\Hooks\Facades\Action::do()
	 *
	 * @param string $hook     The name of the action to execute.
	 * @param mixed  ...$args Optional. The arguments to pass to the callbacks.
	 */
	function doAction(string $hook, mixed ...$args): void
	{
		Action::do($hook, ...$args);
	}
}

if (! function_exists('addFilter')) {
	/**
	 * Adds a callback to a specific filter hook.
	 *
	 * @since 1.0.0
	 * @uses \ArtisanPackUI\Hooks\Facades\Filter::add()
	 *
	 * @param string   $hook     The name of the filter.
	 * @param callable $callback The callback to be executed.
	 * @param int      $priority Optional. The priority of the callback. Default 10.
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
	 * @uses \ArtisanPackUI\Hooks\Facades\Filter::apply()
	 *
	 * @param string $hook     The name of the filter to apply.
	 * @param mixed  $value    The initial value to be filtered.
	 * @param mixed  ...$args Optional. Additional arguments to pass to the callbacks.
	 * @return mixed The filtered value.
	 */
	function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
	{
		return Filter::apply($hook, $value, ...$args);
	}
}
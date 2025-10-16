<?php
/**
 * Filter Hooks Manager
 *
 * Manages the registration and execution of filter hooks.
 *
 * @package    ArtisanPackUI\Hooks
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks;

use Illuminate\Contracts\Container\Container;

/**
 * Manages the registration and execution of filter hooks.
 *
 * @since 1.0.0
 */
class Filter
{
	/**
	 * The application container instance.
	 *
	 * @since 1.0.0
	 * @var   Container
	 */
	protected Container $app;

	/**
	 * Registered filter hooks.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected array $filters = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $app The application container.
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Adds a callback to a specific filter hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $hook     The name of the filter.
	 * @param callable $callback The callback to be executed.
	 * @param int      $priority Optional. The priority of the callback. Default 10.
	 */
	public function add(string $hook, callable $callback, int $priority = 10): void
	{
		$this->filters[$hook][$priority][] = $callback;
	}

	/**
	 * Applies all registered callbacks to a filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook     The name of the filter to apply.
	 * @param mixed  $value    The initial value to be filtered.
	 * @param mixed  ...$args  Optional. Additional arguments to pass to the callbacks.
	 * @return mixed The filtered value.
	 */
	public function apply(string $hook, mixed $value, ...$args): mixed
	{
		if (!isset($this->filters[$hook])) {
			return $value;
		}

		ksort($this->filters[$hook]);
		$callbacks = array_merge(...$this->filters[$hook]);

		// Prepend the value to the arguments array for the callbacks.
		$allArgs = array_merge([$value], $args);

		foreach ($callbacks as $callback) {
			$value = call_user_func_array($callback, $allArgs);
			// Update the value for the next callback in the chain.
			$allArgs[0] = $value;
		}

		return $value;
	}
}
<?php
/**
 * Action Hooks Manager
 *
 * Manages the registration and execution of action hooks, leveraging
 * Laravel's service container for dependency injection in callbacks.
 *
 * @package    ArtisanPackUI\Hooks
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks;

use Illuminate\Contracts\Container\Container;
use ReflectionFunction;

/**
 * Manages the registration and execution of action hooks.
 *
 * @since 1.0.0
 */
class Action
{
	/**
	 * The application container instance.
	 *
	 * @since 1.0.0
	 * @var   Container
	 */
	protected Container $app;

	/**
	 * Registered action hooks.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected array $actions = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $app The application container.
	 */
	public function __CONSTRUCT(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Adds a callback to a specific action hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $hook     The name of the action.
	 * @param callable $callback The callback to be executed.
	 * @param int      $priority Optional. The priority of the callback. Default 10.
	 */
	public function add(string $hook, callable $callback, int $priority = 10): void
	{
		$this->actions[$hook][$priority][] = $callback;
	}

	/**
	 * Executes all registered callbacks for a given action.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook     The name of the action to execute.
	 * @param mixed  ...$args Optional. The arguments to pass to the callbacks.
	 */
	public function do(string $hook, mixed ...$args): void
	{
		if (!isset($this->actions[$hook])) {
			return;
		}

		ksort($this->actions[$hook]);
		$callbacks = array_merge(...$this->actions[$hook]);

		foreach ($callbacks as $callback) {
			$callback(...$args);
		}
	}

	/**
	 * Removes a specific callback from an action hook.
	 *
	 * @since 1.1.0
	 *
	 * @param string   $hook     The name of the action.
	 * @param callable $callback The specific callback to remove.
	 * @param int      $priority Optional. The priority of the callback. Default 10.
	 * @return bool True on success, false on failure.
	 */
	public function remove(string $hook, callable $callback, int $priority = 10): bool
	{
		if (!isset($this->actions[$hook][$priority])) {
			return false;
		}

		foreach ($this->actions[$hook][$priority] as $key => $registeredCallback) {
			if ($registeredCallback === $callback) {
				unset($this->actions[$hook][$priority][$key]);

				// If the priority level is now empty, remove it to keep the array clean.
				if (empty($this->actions[$hook][$priority])) {
					unset($this->actions[$hook][$priority]);
				}
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes all callbacks for a specific action hook or a specific priority.
	 *
	 * @since 1.1.0
	 *
	 * @param string    $hook     The name of the action.
	 * @param int|false $priority Optional. A specific priority to remove. If false, all priorities are removed. Default false.
	 * @return bool True if callbacks were removed, false otherwise.
	 */
	public function removeAll(string $hook, int|false $priority = false): bool
	{
		if (!isset($this->actions[$hook])) {
			return false;
		}

		if (false !== $priority && isset($this->actions[$hook][$priority])) {
			unset($this->actions[$hook][$priority]);
		} elseif (false === $priority) {
			unset($this->actions[$hook]);
		}

		return true;
	}
}
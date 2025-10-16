<?php
/**
 * Base Test Case
 *
 * Provides the basic application bootstrapping required for package testing.
 *
 * @package    Tests
 * @since      1.0.0
 */

namespace Tests;

use ArtisanPackUI\Hooks\BladeDirectiveServiceProvider;
use ArtisanPackUI\Hooks\HooksServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for the Hooks package.
 *
 * @since 1.0.0
 */
class TestCase extends Orchestra
{
	/**
	 * Setup the test environment.
	 *
	 * This method is called before each test.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function setUp(): void
	{
		// This line is absolutely essential. It tells Orchestra Testbench
		// to build the temporary Laravel application and create $this->app.
		parent::setUp();
	}

	/**
	 * Get package providers.
	 *
	 * Tells Testbench which service providers to load for the tests.
	 *
	 * @since 1.0.0
	 *
	 * @param \Illuminate\Foundation\Application $app The application instance.
	 * @return array<int, class-string>
	 */
	protected function getPackageProviders($app): array
	{
		return [
			HooksServiceProvider::class,
			BladeDirectiveServiceProvider::class,
		];
	}
}
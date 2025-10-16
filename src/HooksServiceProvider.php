<?php

namespace ArtisanPackUI\Hooks;

use Illuminate\Support\ServiceProvider;

class HooksServiceProvider extends ServiceProvider
{

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

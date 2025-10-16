<?php

namespace ArtisanPackUI\Hooks;

use Illuminate\Support\ServiceProvider;

class HooksServiceProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->singleton( 'hooks', function ( $app ) {
			return new Hooks();
		} );
	}
}

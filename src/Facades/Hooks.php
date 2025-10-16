<?php

namespace ArtisanPackUI\Hooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ArtisanPackUI\Hooks\A11y
 */
class Hooks extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'hooks';
	}
}

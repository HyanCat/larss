<?php

namespace Hyancat\Larss;


use Illuminate\Support\Facades\Facade;

class LarssFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'larss';
	}
}
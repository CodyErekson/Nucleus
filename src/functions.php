<?php
/**
 * A place to define any needed global functions
 */


if ( !function_exists('d' )) {
	/**
	 * Dump the passed variables
	 *
	 * @param  mixed
	 * @return void
	 */
	function d(... $args)
	{
		foreach ($args as $x) {
			(new Illuminate\Support\Debug\Dumper)->dump($x);
		}
	}
}

if ( !function_exists('dd' )) {
	/**
	 * Dump the passed variables and die
	 *
	 * @param  mixed
	 * @return void
	 */
	function dd(... $args)
	{
		function d(... $args)
		{
			foreach ($args as $x) {
				(new Illuminate\Support\Debug\Dumper)->dump($x);
			}
		}
		die();
	}
}

if ( !function_exists('ddd') ){
	function ddd(... $args)
	{
		foreach ($args as $x) {
			new \dBug\dBug($x);
		}
	}
}
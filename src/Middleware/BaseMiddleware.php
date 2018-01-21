<?php
/**
 * Middleware template class that accepts DIC and makes it available as member variable
 */

namespace Nucleus\Middleware;

/**
 * Class BaseMiddleware
 * @package Nucleus\Middleware
 */
class BaseMiddleware
{
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}
}
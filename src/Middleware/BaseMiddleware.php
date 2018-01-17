<?php

namespace Nucleus\Middleware;

class BaseMiddleware
{
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}
}
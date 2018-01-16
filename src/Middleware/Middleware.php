<?php

namespace Nucleus\Middleware;

class Middleware
{
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}
}
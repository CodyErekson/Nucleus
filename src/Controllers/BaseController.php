<?php

namespace Nucleus\Controllers;

class BaseController

{
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}
}
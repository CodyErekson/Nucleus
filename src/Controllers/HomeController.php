<?php

namespace Nucleus\Controllers;

class HomeController
{
	protected $renderer;
	protected $loggers = [];

	public function __construct(\Slim\Views\PhpRenderer $renderer)
	{
		$this->renderer = $renderer;
	}

	public function addLogger($logger, $name)
	{
		$this->loggers[$name] = $logger;
	}

	public function home($request, $response, $args)
	{
		$this->loggers['debug.log']->debug("here");
		return $this->renderer->render($response, 'index.phtml', $args);
	}
}
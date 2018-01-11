<?php

namespace Nucleus\Controllers;

class HomeController
{
	protected $view;
	protected $loggers = [];

	public function __construct(\Slim\Views\Twig $view)
	{
		$this->view = $view;
	}

	public function addLogger($logger, $name)
	{
		$this->loggers[$name] = $logger;
	}

	public function home($request, $response, $args)
	{
		$this->loggers['debug.log']->debug("here");
		return $this->view->render($response, 'index.html', $args);
	}
}
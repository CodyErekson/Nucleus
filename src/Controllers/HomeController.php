<?php

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;

class HomeController extends BaseController
{
	public function home($request, $response, $args)
	{

		ddd($request, $args);
		$this->container->flash->addMessage('info', 'Test flash message');
		$this->container->flash->addMessage('error', 'Test flash error');
		$this->container['debug.log']->debug("hello " . print_r($args, true));
		return $this->container->view->render($response, 'index.html.twig', $args);
	}
}
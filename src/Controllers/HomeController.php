<?php

namespace Nucleus\Controllers;

class HomeController extends BaseController
{
	public function home($request, $response, $args)
	{
		ddd($request, $args);
		$this->container['debug.log']->debug("hello " . print_r($args, true));
		return $this->container->view->render($response, 'index.html', $args);
	}
}
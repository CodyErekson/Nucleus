<?php

namespace Nucleus\Controllers;

class HomeController extends BaseController
{
	public function home($request, $response, $args)
	{
		$this->container['debug.log']->debug("hello " . print_r($args));
		return $this->container->view->render($response, 'index.html', $args);
	}
}
<?php

namespace Nucleus\Middleware;

class ValidationMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{

		if ( isset($_SESSION['errors']) ) {
			$this->container->view->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
			unset($_SESSION['errors']);
		}

		$response = $next($request, $response);

		return $response;
	}

}
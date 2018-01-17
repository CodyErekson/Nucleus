<?php

namespace Nucleus\Middleware;

class AdminMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{

		if ( $this->container->auth->getRole() != "admin" ){
			$this->container->flash->addMessage('error', 'This page is only available to administrators.');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}

		$response = $next($request, $response);

		return $response;
	}

}
<?php

namespace Nucleus\Middleware;

class AuthMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{

		if ( !$this->container->auth->check() ){
			$this->container->flash->addMessage('error', 'Please login before proceeding.');
			return $response->withRedirect($this->container->router->pathFor('auth.login'));
		}

		$response = $next($request, $response);

		return $response;
	}

}
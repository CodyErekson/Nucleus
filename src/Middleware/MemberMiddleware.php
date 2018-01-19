<?php

namespace Nucleus\Middleware;

class MemberMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{

		if ( !$this->container->user_manager->check() ){
			$this->container->flash->addMessage('error', 'Please login before proceeding.');
			return $response->withRedirect($this->container->router->pathFor('auth.login'));
		}

		$response = $next($request, $response);

		return $response;
	}

}
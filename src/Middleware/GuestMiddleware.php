<?php

namespace Nucleus\Middleware;

class GuestMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{

		if ( $this->container->auth->check() ){
			return $response->withRedirect($this->container->router->pathFor('home'));
		}

		$response = $next($request, $response);

		return $response;
	}

}
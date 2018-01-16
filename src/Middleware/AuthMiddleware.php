<?php

namespace Nucleus\Middleware;

class AuthMiddleware extends Middleware
{

	public function __invoke($request, $response, $next)
	{



		$response = $next($request, $response);

		return $response;
	}

}
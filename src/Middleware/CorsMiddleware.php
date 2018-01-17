<?php

namespace Nucleus\Middleware;

class CorsMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{

		$response = $next($request, $response);
		return $response
			->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
	}

}
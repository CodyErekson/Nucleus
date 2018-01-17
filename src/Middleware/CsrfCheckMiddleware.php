<?php

namespace Nucleus\Middleware;

class CsrfCheckMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{


		if (false === $request->getAttribute('csrf_status')) {
			// display suitable error here
			$this->container->flash->addMessage('error', 'Form security timed out. Please try again.');
			return $response->withRedirect($request->getUri()->getPath()); //redirect back where they came from
		} else {
			// successfully passed CSRF check
			$response = $next($request, $response);
			return $response;
		}
	}

}
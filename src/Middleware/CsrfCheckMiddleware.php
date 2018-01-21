<?php
/**
 * Check the validity of the CSRF tokens
 */

namespace Nucleus\Middleware;

/**
 * Class CsrfCheckMiddleware
 * @package Nucleus\Middleware
 */
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
			return $next($request, $response);
		}
	}

}
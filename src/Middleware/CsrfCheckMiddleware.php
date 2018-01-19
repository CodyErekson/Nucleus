<?php

namespace Nucleus\Middleware;

class CsrfCheckMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{
		// define routes that do not need a csrf check
		/*$passthrough = [
			'/api/user/login/',
			'/api/user/'
		];*/

		/*$this->container['debug.log']->debug($request->getParam('csrf_name'));

		if ( ( is_null($request->getParam('csrf_name')) ) && ( is_null($request->getParam('csrf_value')) ) ) {

		//if ( in_array($request->getUri()->getPath(), $passthrough) ){
			return $next($request, $response);
		}*/

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
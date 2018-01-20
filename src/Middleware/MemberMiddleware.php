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

		$this->container->view->getEnvironment()->addGlobal('user', $this->container->user_manager->currentUser()->toArray());
		$response = $next($request, $response);

		return $response;
	}

}
<?php

namespace Nucleus\Middleware;

class AdminMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{
		$roles = $this->container->user_manager->currentUser()->getRoles();
		$this->container['debug.log']->debug("Roles", $roles);
		if ( in_array("admin", $roles) ){
			$this->container->flash->addMessage('error', 'This page is only available to administrators.');
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(401);
			$res->getBody()->write(json_encode(['error' => 'This page is only available to administrators.']));
			return $res;
			//return $response->withRedirect($this->container->router->pathFor('home'));
		}

		$response = $next($request, $response);

		return $response;
	}

}
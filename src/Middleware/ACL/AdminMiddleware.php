<?php
/**
 * Control access for routes requiring admin role
 */

namespace Nucleus\Middleware\ACL;

use Nucleus\Middleware\BaseMiddleware;

/**
 * Class AdminMiddleware
 * @package Nucleus\Middleware\ACL
 */
class AdminMiddleware extends BaseMiddleware
{

	public function __invoke($request, $response, $next)
	{
		$roles = $this->container->user_manager->currentUser()->getRoles();
		$this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nRoles", $roles);
		if ( in_array("admin", $roles) ){
			$this->container->flash->addMessage('error', 'This page is only available to administrators.');
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(401);
			$res->getBody()->write(json_encode(['error' => 'This page is only available to administrators.']));
			return $res;
		}

		$response = $next($request, $response);

		return $response;
	}

}
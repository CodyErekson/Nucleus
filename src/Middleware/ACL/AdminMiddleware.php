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
        if ( (isset($roles['admin'])) && ($roles['admin']) ){
            // User is an admin
            $this->container->user_manager->currentUser()->setAdmin(true);
            $response = $next($request, $response);
            return $response;
        } else {
            $this->container->flash->addMessage('error', 'This page is only available to administrators.');
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(401);
            $res->getBody()->write(json_encode(['error' => 'This page is only available to administrators.']));
            return $res;
        }
    }
}

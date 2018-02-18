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
        // We know user is logged in now, just find out if they are an admin
        if ($this->container->user_manager->currentUser()->getAdmin()) {
            $response = $next($request, $response);
            return $response;
        }

        $this->container->flash->addMessage('error', 'This page is only available to administrators.');
        $response = $response->withHeader("Content-Type", "application/json");
        $response = $response->withStatus(401);
        $response->getBody()->write(json_encode(['error' => 'This page is only available to administrators.']));
        return $response;
    }
}

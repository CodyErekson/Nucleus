<?php
/**
 * Control access for routes only available to guests (ie, not logged in users)
 */

namespace Nucleus\Middleware\ACL;

use Nucleus\Middleware\BaseMiddleware;

/**
 * Class GuestMiddleware
 * @package Nucleus\Middleware\ACL
 */
class GuestMiddleware extends BaseMiddleware
{

    public function __invoke($request, $response, $next)
    {
        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\n");
        // Redirect to home page if user is logged in
        if ($this->container->user_manager->check()) {
            return $response->withRedirect($this->container->router->pathFor('home'));
        }

        $response = $next($request, $response);

        return $response;
    }
}

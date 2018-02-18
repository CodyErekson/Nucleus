<?php
/**
 * Control access for routes requiring member role
 */

namespace Nucleus\Middleware\ACL;

use Nucleus\Middleware\BaseMiddleware;

/**
 * Class MemberMiddleware
 * @package Nucleus\Middleware\ACL
 */
class MemberMiddleware extends BaseMiddleware
{

    public function __invoke($request, $response, $next)
    {

        if (!$this->container->user_manager->check()) {
            $this->container->flash->addMessage('error', 'Please login before proceeding.');
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        } else {
            // There is a valid session, make sure the token is stored
            if (!isset($_COOKIE['token'])) {
                $this->container->user_manager->login($this->container->user_manager->currentUser()->uuid);
            }
        }

        $this->container->view->getEnvironment()->addGlobal(
            'auth',
            [
                'check' => $this->container->user_manager->check(),
                'user' => $this->container->user_manager->currentUser(),
                'roles' => $this->container->user_manager->currentUser()->getRoles()
            ]
        );
        $response = $next($request, $response);

        return $response;
    }
}

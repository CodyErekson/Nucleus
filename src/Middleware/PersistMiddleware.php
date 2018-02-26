<?php
/**
 * Acquire and make filled-in form field values available to the Twig template environment
 */

namespace Nucleus\Middleware;

/**
 * Class PersistMiddleware
 * @package Nucleus\Middleware
 */
class PersistMiddleware extends BaseMiddleware
{

    public function __invoke($request, $response, $next)
    {
        if (isset($_SESSION['old'])) {
            $this->container->view->getEnvironment()->addGlobal('old', $_SESSION['old']);
        }
        $_SESSION['old'] = $request->getParams();

        $response = $next($request, $response);

        return $response;
    }
}

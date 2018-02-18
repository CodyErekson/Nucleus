<?php
/**
 * Create some template variables based upon current request
 */

namespace Nucleus\Middleware;

/**
 * Class TemplateVariableMiddleware
 * @package Nucleus\Middleware
 */
class TemplateVariableMiddleware extends BaseMiddleware
{

    public function __invoke($request, $response, $next)
    {
        if (!is_null($request->getAttribute('route'))) {
            $this->container->view->getEnvironment()->addGlobal('pageName', $request->getAttribute('route')->getName());
        }

        return $next($request, $response);
    }
}

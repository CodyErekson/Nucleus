<?php
/**
 * Register event emitter listeners
 */

namespace Nucleus\Middleware;

/**
 * Class EmitterMiddleware
 * @package Nucleus\Middleware
 */
class EmitterMiddleware extends BaseMiddleware
{

    public function __invoke($request, $response, $next)
    {
        // This is how to register an event listener
        $this->container->emitter->addListener("event.test", $this->container['listener.test']);

        return $next($request, $response);
    }
}

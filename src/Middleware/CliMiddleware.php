<?php
/**
 * Register event emitter listeners
 */

namespace Nucleus\Middleware;

/**
 * Class CliMiddleware
 * @package Nucleus\Middleware
 */
class CliMiddleware extends BaseMiddleware
{

    public function __invoke($request, $response, $next)
    {
        if ((PHP_SAPI == 'cli-server') || (PHP_SAPI == 'cli')) {
            $this->defaultOutput();
        }

        return $next($request, $response);
    }

    private function defaultOutput()
    {
        $env = $this->container->get('settings')['env'];
        $path = realpath($env['env_path'] . '/bin');
        $this->container->cli->addArt($path);
        $this->container->cli->backgroundBlue()->red()->draw('title');
        $this->container->cli->green()->inline("v" . $this->container->composer['version']);
        $this->container->cli->out(" by Cody Erekson")->br();
    }
}

<?php
/**
 * Middleware template class that accepts DIC and makes it available as member variable
 */

namespace Nucleus\Middleware;

use \Interop\Container\ContainerInterface;

/**
 * Class BaseMiddleware
 * @package Nucleus\Middleware
 */
class BaseMiddleware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}

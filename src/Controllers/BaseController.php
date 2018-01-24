<?php
/**
 * Controller template class that accepts DIC and makes it available as member variable
 */

namespace Nucleus\Controllers;

/**
 * Class BaseController
 * @package Nucleus\Controllers
 */
class BaseController
{
    protected $container;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }
}

<?php
/**
 * Controller template class that accepts DIC and makes it available as member variable
 */

namespace Nucleus\Controllers;

use \Interop\Container\ContainerInterface;

/**
 * Class BaseController
 * @package Nucleus\Controllers
 */
class BaseController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}

<?php
// A base class to build CLI commands off of

namespace Nucleus\Helpers\Commands;

use \Interop\Container\ContainerInterface;

class BaseCommand
{

    /** @var ContainerInterface */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        // access container classes
        // eg $container->get('redis');
        $this->container = $container;
    }

    public function command($arguments)
    {
    }

}
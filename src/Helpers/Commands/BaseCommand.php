<?php
// A base class to build CLI commands off of

namespace Nucleus\Helpers\Commands;

use \Interop\Container\ContainerInterface;
use Slim\Exception\ContainerValueNotFoundException;

class BaseCommand
{

    /** @var ContainerInterface */
    protected $container;
    protected $cli;

    /**
     * Constructor
     *
     * @param \Slim\Container $container
     * @return void
     */
    public function __construct(\Slim\Container $container)
    {
        // access container classes
        // eg $container->get('redis');
        $this->container = $container;
        if (isset($this->container->cli)) {
            $this->cli = $this->container->cli;
        } else {
            throw new ContainerValueNotFoundException('CLI handler not defined.');
        }
    }

    public function command($arguments)
    {
    }
}

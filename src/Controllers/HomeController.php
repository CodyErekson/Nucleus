<?php
/**
 * Controller for home route -- mostly used for testing at this point
 */

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Nucleus\Models\Role;
use phpDocumentor\Reflection\Types\Null_;
use Twig\Cache\NullCache;

/**
 * Class HomeController
 * @package Nucleus\Controllers
 */
class HomeController extends BaseController
{
    public function home($request, $response, $args)
    {
        return $this->container->view->render($response, 'home.twig', $args);
    }

    /**
     * Testing the event/emitter model
     * @param $request
     * @param $response
     * @param $arguments
     */
    public function eventTest($request, $response, $arguments)
    {
        $this->container->emitter->emit("event.test");
    }
}

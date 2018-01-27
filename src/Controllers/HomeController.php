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
        if (!$this->container->user_manager->check()) {
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }
        /*$roles = Role::where('id', '3')->first();
        foreach($roles->users as $role){
            ddd($role->username);
        }*/
        //$user = User::where('uuid', 'd7af33a6-77a0-56a3-8f2e-f14fc9049c17')->first();
        $user = User::where('username', 'Bobby')->first();
        $user->setContainer($this->container);
        ///ddd($user->getToken()->token);

        //$this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nhello " . print_r($args, true));
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

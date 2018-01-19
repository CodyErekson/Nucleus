<?php

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Nucleus\Models\Role;
use phpDocumentor\Reflection\Types\Null_;
use Twig\Cache\NullCache;

class HomeController extends BaseController
{
	public function home($request, $response, $args)
	{
		/*$roles = Role::where('id', '3')->first();
		foreach($roles->users as $role){
			ddd($role->username);
		}*/
		//$user = User::where('uuid', 'd7af33a6-77a0-56a3-8f2e-f14fc9049c17')->first();
		$user = User::where('username', 'Bobby')->first();
		$user->setContainer($this->container);
		ddd($user->getToken()->token);

		//$this->container['debug.log']->debug("hello " . print_r($args, true));
		return $this->container->view->render($response, 'index.html.twig', $args);
	}
}
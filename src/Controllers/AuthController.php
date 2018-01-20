<?php

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Respect\Validation\Validator as v;

class AuthController extends BaseController
{

	public function getSignUp($request, $response)
	{
		return $this->container->view->render($response, 'signup.twig');
	}

	public function postSignUp($request, $response)
	{
		$this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nCreate user payload:", $request->getParsedBody());

		if ( !$this->container->user_manager->createUserValidation($request) ){
			return $response->withRedirect($this->container->router->pathFor('auth.signup'));
		}

		try {
			$user = $this->container->user_manager->createUser($request);
			$this->container->user_manager->login($user->uuid);
		} catch ( \Illuminate\Database\QueryException $e ){
			return $response->withRedirect($this->container->router->pathFor('auth.signup'));
		}

		$this->container->flash->addMessage('success', 'Your account has been successfully created.');

		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getLogin($request, $response)
	{
		return $this->container->view->render($response, 'login.twig');
	}

	public function postLogin($request, $response)
	{
		$this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nLogin payload:", $request->getParsedBody());

		if ( !$this->container->user_manager->loginValidation($request) ){
			return $response->withRedirect($this->container->router->pathFor('auth.login'));
		}

		try {
			$user = \Nucleus\Models\User::where('username', $request->getParam('username'))->where('active', true)->first();
			$this->container->user_manager->login($user->uuid);
		} catch ( \Exception $e ){
			$this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
			return $response->withRedirect($this->container->router->pathFor('auth.login'));
		}

		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getLogout($request, $response)
	{
		$this->container->user_manager->logout();

		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getUpdateUser($request, $response)
	{
		return $this->container->view->render($response, 'update_profile.twig');
	}

	public function postUpdateUser($request, $response)
	{
		$this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nUpdate user payload:", $request->getParsedBody());

		$user = $this->container->user_manager->currentUser();
		if ( !$this->container->user_manager->updateUserValidation($request, $user->uuid) ){
			return $response->withRedirect($this->container->router->pathFor('auth.user.update'));
		}

		try {
			$user = $this->container->user_manager->updateUser($request->getParsedBody(), $user->uuid);
			$this->container->user_manager->login($user->uuid);
		} catch ( \Illuminate\Database\QueryException $e ){
			return $response->withRedirect($this->container->router->pathFor('auth.user.update'));
		}

		$this->container->flash->addMessage('success', 'Your account has been successfully modified.');

		return $response->withRedirect($this->container->router->pathFor('auth.user.update'));
	}

	public function getPasswordChange($request, $response)
	{
		return $this->container->view->render($response, 'change_password.twig');
	}

	public function postPasswordChange($request, $response)
	{
		if ( !$this->container->user_manager->check() ){
			$this->container->flash->addMessage('error', 'Must be logged in to change password.');
			return $response->withRedirect($this->container->router->pathFor('auth.user.password'));
		}

		if ( !$this->container->user_manager->changePasswordValidation($request) ){
			$this->container->flash->addMessage('error', 'Unable to change password.');
			return $response->withRedirect($this->container->router->pathFor('auth.user.password'));
		}

		$this->container->user_manager->currentUser()->setPassword($request->getParam('password'));

		$this->container->flash->addMessage('success', 'Your password has been changed.');

		return $response->withRedirect($this->container->router->pathFor('home'));
	}
}
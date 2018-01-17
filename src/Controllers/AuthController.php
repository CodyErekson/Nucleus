<?php

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Respect\Validation\Validator as v;

class AuthController extends BaseController
{

	public function getSignUp($request, $response)
	{
		return $this->container->view->render($response, 'auth/signup.html.twig');
	}

	public function postSignUp($request, $response)
	{
		$validation = $this->container->validator->validate($request, [
			'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable(),
			'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable(),
			'password' => v::notEmpty()->length(8)->noWhitespace()->equals($request->getParam('confirm')),
			'confirm' => v::notEmpty()->length(8)->noWhitespace(),
		]);

		if ( $validation->failed()) {
			return $response->withRedirect($this->container->router->pathFor('auth.signup'));
		}

		// TODO -- get uuid
		$user = User::create([
			'username' => $request->getParam('username'),
			'email' => $request->getParam('email'),
			'password' => password_hash($request->getParam('password'),PASSWORD_BCRYPT)
		]);

		$this->container->flash->addMessage('success', 'Your account has been successfully created.');

		$this->auth->validate($user->username, $request->getParam('password'));

		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getLogin($request, $response)
	{
		return $this->container->view->render($response, 'auth/login.html.twig');
	}

	public function postLogin($request, $response)
	{
		$valid = $this->container->auth->validate(
			$request->getParam('username'),
			$request->getParam('password')
		);

		if ( !$valid ){
			$this->container->flash->addMessage('error', 'Incorrect login credentials.');
			return $response->withRedirect($this->container->router->pathFor('auth.login'));
		}

		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getLogout($request, $response)
	{
		$this->container->auth->logout();

		return $response->withRedirect($this->container->router->pathFor('home'));
	}

	public function getPasswordChange($request, $response)
	{
		return $this->container->view->render($response, 'auth/password.html.twig');
	}

	public function postPasswordChange($request, $response)
	{
		if ( !$this->container->auth->check() ){
			$this->container->flash->addMessage('error', 'Must be logged in to change password.');
			return $response->withRedirect($this->container->router->pathFor('auth.password.change'));
		}

		$validation = $this->container->validator->validate($request, [
			'current' => v::notEmpty()->length(8)->noWhitespace(),
			'password' => v::notEmpty()->length(8)->noWhitespace()
				->equals($request->getParam('confirm'))->passwordCheck($request->getParam('current')),
			'confirm' => v::notEmpty()->length(8)->noWhitespace(),
		]);

		if ( $validation->failed()) {
			return $response->withRedirect($this->container->router->pathFor('auth.password.change'));
		}

		$this->container->auth->user()->setPassword($request->getParam('password'));

		$this->container->flash->addMessage('success', 'Your password has been changed.');

		return $response->withRedirect($this->container->router->pathFor('home'));
	}
}
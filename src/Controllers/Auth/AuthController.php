<?php

namespace Nucleus\Controllers\Auth;

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
			'username' => v::notOptional()->notEmpty()->stringType()->alnum(),
			'email' => v::notOptional()->noWhitespace()->notEmpty()->email(),
			'password' => v::notOptional()->noWhitespace()->notEmpty()->equals($request->getParam('confirm')),
			'confirm' => v::notOptional()->noWhitespace()->notEmpty(),
		]);

		if ( $validation->failed()) {
			return $response->withRedirect($this->container->router->pathFor('auth.signup'));
		}

		/*$user = User::create([
			'username' => $request->getParam('username'),
			'email' => $request->getParam('email'),
			'password' => password_hash($request->getParam('password'),PASSWORD_BCRYPT)
		]);*/

		return $response->withRedirect($this->container->router->pathFor('home'));
	}
}
<?php

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Respect\Validation\Validator as v;

class UserController extends BaseController
{

	protected $token;

	public function setToken($token)
	{
		$this->token = $token;
	}

	public function login($request, $response, $args)
	{
		$data = $request->getParsedBody();

		$validation = $this->container->validator->validate($request, [
			'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameExists(),
			'password' => v::notEmpty()->length(8)->noWhitespace()->stringType()
				->passwordCheck($data['username']),
		]);

		if ( $validation->failed()) {
			return $response->withStatus(401)->withJson($_SESSION['errors']);
		}

		$user = \Nucleus\Models\User::where('username', '=', $data['username'])->first();

		if ( is_null($user) ) {
			$this->container['debug.log']->debug($data['username'] . " not found in users");
			return $response->withStatus(401)->withJson(["error" => $data['username'] . " not found in users"]);
		} else {
			$this->container->user_manager->login($user->uuid);
			$user->setContainer($this->container);
			// Find a corresponding token
			$this->container['debug.log']->debug('Looking for token:', $user->toArray());
			if ( !$user->getToken() ){
				$this->container['debug.log']->debug("Unable to retrieve a token.", ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
				$this->container['error.log']->error("Unable to retrieve a token.", ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
				return $response->withStatus(400);
			}
			$this->container['debug.log']->debug("Token: " . $user->token->token);
			$out = [
				"token" => $user->token->token,
				"username" => $user->username
			];
			return $response->withJson($out);
		}
	}

	public function logout($request, $response, $args)
	{
		$this->container['debug.log']->debug($this->container->token->context->user->uuid);
		$uuid = $this->token->context->user->uuid;
		$tm = $this->container->token_manager;
		$tm->setUserId($uuid);
		$tm->flush();
		return $response->withStatus(200);
	}

	public function getUsers($request, $response, $args)
	{
		return $response->getBody()->write(\Nucleus\Models\User::all()->toJson());
	}

	public function getUser($request, $response, $args)
	{
		$uuid = $args['uuid'];
		$dev = \Nucleus\Models\User::find($uuid);
		$response->getBody()->write($dev);
		return $response;
	}

	public function createUser($request, $response, $args)
	{
		$this->container['debug.log']->debug("Create user payload:", $request->getParsedBody());

		if ( !$this->container->user_manager->createUserValidation($request) ){
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(400);
			$res->getBody()->write(json_encode(["errors" => $_SESSION['errors']]));
			return $res;
		}

		try {
			$user = $this->container->user_manager->createUser($request);
		} catch ( \Illuminate\Database\QueryException $e ){
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(400);
			$res->getBody()->write(json_encode(["error" => $e->getMessage()]));
			return $res;
		}

		return $response->withStatus(201)->getBody()->write($user->toJson());
	}

	public function updateUser($request, $response, $args)
	{
		$this->container['debug.log']->debug("Update user payload:", $request->getParsedBody());

		$uuid = $args['uuid'];
		$data = $request->getParsedBody();

		if ( !$this->container->user_manager->updateUserValidationAdmin($request, $uuid) ){
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(400);
			$res->getBody()->write(json_encode(["errors" => $_SESSION['errors']]));
			return $res;
		}

		$user = $this->container->user_manager->updateUser($uuid, $data);

		return $response->getBody()->write($user->toJson());
	}

	public function deactivateUser($request, $response, $args)
	{
		$uuid = $args['uuid'];

		$this->container['debug.log']->debug("Deactivate user:", [$uuid]);

		$user = $this->container->user_manager->setActive($uuid, false);

		return $response->getBody()->write($user->toJson());
	}

	public function activateUser($request, $response, $args)
	{
		$uuid = $args['uuid'];

		$user = $this->container->user_manager->setActive($uuid, true);

		$this->container['debug.log']->debug("Activate user:", $user->toArray());

		return $response->getBody()->write($user->toJson());
	}

	public function deleteUser($request, $response, $args)
	{
		$uuid = $args['uuid'];

		$this->container['debug.log']->debug("Delete user:", $uuid);

		if ( $this->container->user_manager->deleteUser($uuid) ){
			return $response->withStatus(200);
		}
		$res = $response->withHeader("Content-Type", "application/json");
		$res = $res->withStatus(400);
		$res->getBody()->write(json_encode(["errors" => "Failed to delete user."]));
		return $res;
	}
}
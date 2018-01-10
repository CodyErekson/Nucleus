<?php

namespace Nucleus\Controllers;

class UserController
{
	protected $renderer;
	protected $user_manager;
	protected $token_manager;
	protected $jwt;
	protected $token;
	protected $uuid;
	protected $loggers = [];

	public function __construct(\Slim\Views\PhpRenderer $renderer,
								\Nucleus\Helpers\UserManager $user_manager,
								\Nucleus\Helpers\TokenManager $token_manager,
								\Firebase\JWT\JWT $jwt,
								$uuid)
	{
		$this->renderer = $renderer;
		$this->user_manager = $user_manager;
		$this->token_manager = $token_manager;
		$this->jwt = $jwt;
		$this->uuid = $uuid;
	}

	public function addLogger($logger, $name)
	{
		$this->loggers[$name] = $logger;
	}

	public function setToken($token)
	{
		$this->token = $token;
	}

	public function home($request, $response, $args)
	{
		$this->loggers['debug.log']->debug("here");
		return $this->renderer->render($response, 'index.phtml', $args);
	}

	public function login($request, $response, $args)
	{
		$data = $request->getParsedBody();
		$users = \Nucleus\Models\User::all();
		$login = $data['username'];
		$password = $data['password'];
		foreach ($users as $key => $user) {
			if ( ( $user->username == $login ) && ( $user->password == md5($password) ) ) {
				$current_user = $user;
				break;
			}
		}
		if (!isset($current_user)) {
			$this->loggers['debug.log']->debug($login . " not found in users");
			return $response->withStatus(401);
		} else {
			// Find a corresponding token
			$this->loggers['debug.log']->debug('Looking for token:', $current_user->toArray());
			try {
				$token = \Nucleus\Models\Token::where('uuid', '=', $current_user->uuid)
					->where('expiration', '>', date('Y-m-d H:i:s'))
					->firstOrFail();
				$tm = $this->token_manager;
				$tm->setUserId($current_user->uuid);
				$tm->cleanExpired();
				$this->loggers['debug.log']->debug('Stored token found', $token->toArray());
				if (count($token)) {
					$out = [
						"token"      => $token->token,
						"username" => $current_user->username
					];
					return $response->withJson($out);
				}
			} catch ( \Illuminate\Database\Eloquent\ModelNotFoundException $e) {
				// Create a new token if a user is found but not a corresponding token
				$key = getenv('JWT_SECRET');
				$payload = array(
					"iss"     => getenv('HOST'),
					"iat"     => time(),
					"exp"     => time() + (3600 * 24 * 15),
					"context" => [
						"user" => [
							"username" => $current_user->username,
							"uuid"    => $current_user->uuid
						]
					]
				);
				try {
					$jwt = $this->jwt::encode($payload, $key);
				} catch (Exception $ee) {
					$this->loggers['debug.log']->debug($ee->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
					$this->loggers['error.log']->error($ee->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
					return $response->withStatus(400);
				}
				$this->loggers['debug.log']->debug('New JWT token', ['token' => $jwt, 'payload' => $data]);
				$token = new \Nucleus\Models\Token;
				$tm = $this->token_manager;
				$tm->setUserId($current_user->uuid);
				$tm->flush();
				$token->uuid = $current_user->uuid;
				$token->token = $jwt;
				$token->expiration = date('Y-m-d H:i:s', $payload['exp']);
				$token->setCreatedAt($payload['iat']);
				$token->save();
				$out = [
					"token"      => $token->token,
					"username" => $current_user->username
				];
				return $response->withJson($out);
			} catch ( Exception $e ){
				$this->loggers['debug.log']->debug($e->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
				$this->loggers['error.log']->error($e->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
				return $response->withStatus(400);
			}
		}
	}

	public function logout($request, $response, $args)
	{
		$this->loggers['debug.log']->debug($this->token->context->user->uuid);
		$uuid = $this->token->context->user->uuid;
		$tm = $this->token_manager;
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
		$data = $request->getParsedBody();
		$user = new \Nucleus\Models\User();
		if ( !isset($data['username']) ){
			return $response->withStatus(400);
		}
		if ( !isset($data['email']) ){
			return $response->withStatus(400);
		}
		if ( !isset($data['password']) ){
			return $response->withStatus(400);
		}
		if ( !isset($data['password_confirm']) ){
			return $response->withStatus(400);
		}
		if ( $data['password'] !== $data['password_confirm']){
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(400);
			$res->getBody()->write(json_encode(["error" => "Password does not match confirmation"]));
			return $res;
		}
		if ( !$this->user_manager->validateUsernameUnique($data['username']) ){
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(400);
			$res->getBody()->write(json_encode(["error" => "Username is not available"]));
			return $res;
		}
		if ( !$this->user_manager->validateEmailUnique($data['email']) ){
			$res = $response->withHeader("Content-Type", "application/json");
			$res = $res->withStatus(400);
			$res->getBody()->write(json_encode(["error" => "Email is associated with existing user"]));
			return $res;
		}
		$user->username = $data['username'];
		$user->email = $data['email'];
		$user->uuid = $this->uuid;
		$user->password = md5($data['password']);

		try {
			$user->save();
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
		$uuid = $args['uuid'];
		$data = $request->getParsedBody();
		$user = \Nucleus\Models\User::find($uuid);
		$user->username = $data['username'] ?: $user->username;

		$user->save();

		return $response->getBody()->write($user->toJson());
	}

	public function deleteUser($request, $response, $args)
	{
		$uuid = $args['uuid'];
		$user = \Nucleus\Models\User::find($uuid);
		//TODO - authorization
		$user->delete();

		return $response->withStatus(200);
	}
}
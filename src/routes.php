<?php
// Routes

// Just a simple testing route
$app->get('/[{name}]', function ($request, $response, $args) {
	// Sample log message
	$uuid = $this->uuid;
	$this['debug.log']->debug($uuid('jimmy'));


	// Render index view
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/test/', function($request, $response) {
	$users = \Nucleus\Models\User::all();
	return $response->getBody()->write($users->toJson());
});

// Authenticate route.
$app->post('/api/user/login/', function ($request, $response) {
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
		$this['debug.log']->debug($login . " not found in users");
		return $response->withStatus(401);
	} else {
		// Find a corresponding token
		$this['debug.log']->debug('Looking for token:', $current_user->toArray());
		try {
			$token = \Nucleus\Models\Token::where('uuid', '=', $current_user->uuid)
				->where('expiration', '>', date('Y-m-d H:i:s'))
				->firstOrFail();
			$tm = $this->token_manager;
			$tm->setUserId($current_user->uuid);
			$tm->cleanExpired();
			$this['debug.log']->debug('Stored token found', $token->toArray());
			if (count($token)) {
				$out = [
					"token"      => $token->token,
					"username" => $current_user->username
				];
				return $response->withJson($out);
			}
		} catch ( Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			// Create a new token if a user is found but not a corresponding token
			//if (count($current_user) != 0 && !count($token_from_db)) {
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
					$jwt = $this['jwt']::encode($payload, $key);
				} catch (Exception $ee) {
					$this['debug.log']->debug($ee->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
					$this['error.log']->error($ee->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
					return $response->withStatus(400);
				}
				$this['debug.log']->debug('New JWT token', ['token' => $jwt, 'payload' => $data]);
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
			//}
		} catch ( Exception $e ){
			$this['debug.log']->debug($e->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
			$this['error.log']->error($e->getMessage(), ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]);
			return $response->withStatus(400);
		}
	}
});

$app->post('/api/user/logout/', function ($request, $response) {
	$this['debug.log']->debug($this->token->context->user->uuid);
	$uuid = $this->token->context->user->uuid;
	$tm = $this->token_manager;
	$tm->setUserId($uuid);
	$tm->flush();
	return $response->withStatus(200);
});

$app->get('/api/user/', function($request, $response) {
	return $response->getBody()->write(\Nucleus\Models\User::all()->toJson());
});

$app->get('/api/user/{id}/', function($request, $response, $args) {
	$id = $args['id'];
	$dev = \Nucleus\Models\User::find($id);
	$response->getBody()->write($dev);
	return $response;
});

$app->post('/api/user/', function($request, $response, $args) {
	$data = $request->getParsedBody();
	$dev = new \Nucleus\Models\User();
	$dev->username = $data['username'];

	$dev->save();

	return $response->withStatus(201)->getBody()->write($dev->toJson());
});

$app->delete('/api/user/{id}/', function($request, $response, $args) {
	$id = $args['id'];
	$dev = \Nucleus\Models\User::find($id);
	$dev->delete();

	return $response->withStatus(200);
});

//remember to include header X-Http-Method-Override:PUT
$app->put('/api/user/{id}/', function($request, $response, $args) {
	$id = $args['id'];
	$data = $request->getParsedBody();
	$dev = \Nucleus\Models\User::find($id);
	$dev->username = $data['username'] ?: $dev->name;

	$dev->save();

	return $response->getBody()->write($dev->toJson());
});
<?php
// Routes

// Just a simple testing route
$app->get('/[{name}]', function ($request, $response, $args) {
	// Sample log message
	//$this->logger->info("Slim-Skeleton '/' route");
print_r($this);


	// Render index view
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/test/', function($request, $response) {
	$users = \Nucleus\Models\User::all();
	return $response->getBody()->write($users->toJson());
});

// Authenticate route.
$app->post('/authenticate/', function ($request, $response) {
	$data = $request->getParsedBody();
	$users = \Nucleus\Models\User::all();
	$login = $data['username'];
	$password = $data['password'];
	foreach ($users as $key => $user) {
		if ( ( $user->username == $login ) && ( $user->password == md5($password) ) ) {
			$current_user = $user;
		}
	}
	if (!isset($current_user)) {
		//echo json_encode("No user found");
		return $response->withStatus(401);
	} else {
		// Find a corresponding token.
		try {
			$token_from_db = false;
			$token_from_db = \Nucleus\Models\Token::where('user_id', '=', $current_user->id)->find(1);
			$this->logger->info(print_r($token_from_db,1));
			if (count($token_from_db)) {
				echo json_encode([
					"token"      => $token_from_db->token,
					"username" => $current_user->username
				]);
			}
		} catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return $response->withStatus(400);
			echo '{"error":{"text":' . $e->getMessage() . '}}';
		}

		// Create a new token if a user is found but not a corresponding token
		if (count($current_user) != 0 && !count($token_from_db)) {
			$key = getenv('JWT_SECRET');
			$payload = array(
				"iss"     => getenv('HOST'),
				"iat"     => time(),
				"exp"     => time() + (3600 * 24 * 15),
				"context" => [
					"user" => [
						"username" => $current_user->username,
						"user_id"    => $current_user->id
					]
				]
			);
			try {
				$jwt = $this['jwt']::encode($payload, $key);
			} catch (Exception $e) {
				return $response->withStatus(400);
				echo json_encode($e);
			}
			$this->logger->info($jwt);
			$token = new \Nucleus\Models\Token;
			$token->user_id = $current_user->id;
			$token->token = $jwt;
			$token->expiration = date('Y-m-d H:i:s', $payload['exp']);
			$this->logger->info(date('Y-m-d H:i:s', $payload['exp']));
			$token->setCreatedAt($payload['iat']);
			$token->save();
			echo json_encode([
				"token"      => $jwt,
				"username" => $current_user['user_id']
			]);
		}
	}
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
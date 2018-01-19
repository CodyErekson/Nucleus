<?php
// Routes

//use Psr\Http\Message\ServerRequestInterface as Request;
//use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Nucleus\Middleware\MemberMiddleware;
use Nucleus\Middleware\GuestMiddleware;
use Nucleus\Middleware\AdminMiddleware;
use \Nucleus\Middleware\CsrfCheckMiddleware;

// Just a simple testing route

$app->get('/[{name}]', 'HomeController:home')->setName('home');

$app->get('/test/', function($request, $response) {
	$data = $request->getParsedBody();
	$user = \Nucleus\Models\User::where('username', '=', $data['username']);
	var_dump($user->exists());
	die();
	if (preg_match("/Bearer\s+(.*)$/i", $request->getHeader("Authorization")[0], $matches))
	{
		$token = \Nucleus\Models\Token::where('token', '=', $matches[1])->first();
		var_dump($token->getUser());
	}
	die();
	$users = \Nucleus\Models\User::all();
	return $response->getBody()->write($users->toJson());
});

/**
 * View routes -- output HTML
 */

// Guest routes
$app->group('', function () {

	$this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');

	$this->post('/auth/signup', 'AuthController:postSignUp');

	$this->get('/auth/login', 'AuthController:getLogin')->setName('auth.login');

	$this->post('/auth/login', 'AuthController:postLogin');

})->add(new GuestMiddleware($container))->add(new CsrfCheckMiddleware($container));

// User routes
$app->group('', function () {

	$this->get('/auth/logout', 'AuthController:getLogout')->setName('auth.logout');

	$this->get('/auth/user/update', 'AuthController:getUpdateUser')->setName('auth.user.update');

	$this->post('/auth/user/update', 'AuthController:postUpdateUser');

	$this->get('/auth/user/password', 'AuthController:getPasswordChange')->setName('auth.user.password');

	$this->post('/auth/user/password', 'AuthController:postPasswordChange');

})->add(new MemberMiddleware($container))->add(new CsrfCheckMiddleware($container));

/**
 * API routes -- output JSON
 */

// Admin routes
$app->group('', function () {

	$this->post('/api/user/', 'UserController:createUser');

	//remember to include header X-Http-Method-Override:PUT, actually use POST
	$this->put('/api/user/{uuid}', 'UserController:updateUser');

	$this->put('/api/user/{uuid}/deactivate/', 'UserController:deactivateUser');

	$this->put('/api/user/{uuid}/activate/', 'UserController:activateUser');

	$this->delete('/api/user/{uuid}', 'UserController:deleteUser');

})->add(new AdminMiddleware($container));

// Authenticate route.
$app->post('/api/user/login/', 'UserController:login');

$app->post('/api/user/logout/', 'UserController:logout');

$app->get('/api/user/', 'UserController:getUsers');

$app->get('/api/user/{uuid}', 'UserController:getUser');



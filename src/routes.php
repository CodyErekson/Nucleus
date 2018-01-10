<?php
// Routes

// Just a simple testing route

$app->get('/[{name}]', 'HomeController:home');

$app->get('/test/', function($request, $response) {
	$users = \Nucleus\Models\User::all();
	return $response->getBody()->write($users->toJson());
});

// Authenticate route.
$app->post('/api/user/login/', 'UserController:login');

$app->post('/api/user/logout/', 'UserController:logout');

$app->get('/api/user/', 'UserController:getUsers');

$app->get('/api/user/{uuid}', 'UserController:getUser');

$app->post('/api/user/', 'UserController:createUser');

//remember to include header X-Http-Method-Override:PUT, actually use POST
$app->put('/api/user/{uuid}', 'UserController:updateUser');

$app->put('/api/user/{uuid}/deactivate/', 'UserController:deactivateUser');

$app->put('/api/user/{uuid}/activate/', 'UserController:activateUser');

$app->delete('/api/user/{uuid}', 'UserController:deleteUser');
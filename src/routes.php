<?php
/**
 * Definition of all routes. To be required by bootstrap.php.
 */

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Nucleus\Middleware\MemberMiddleware;
use Nucleus\Middleware\GuestMiddleware;
use Nucleus\Middleware\AdminMiddleware;
use \Nucleus\Middleware\CsrfCheckMiddleware;

// Just a simple testing route

$app->get('/[{name}]', 'HomeController:home')->setName('home');

// An event emitter test
$app->get('/event/', 'HomeController:eventTest');

$app->get('/test/', function ($request, $response) {
    $routes = $this->router->getRoutes();
    // And then iterate over $routes

    foreach ($routes as $route) {
        echo $route->getPattern() . " -- " . $route->getName() .  "<br>";
    }
    dd();
    /*$data = $request->getParsedBody();
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
    return $response->getBody()->write($users->toJson());*/
    $args = [];
    return $this->view->render($response, 'test_page.twig', $args);
});

/**
 * View routes -- output HTML
 */

// Guest only routes
$app->group('', function () {

    $this->get('/auth/signup/', 'AuthController:getSignUp')->setName('auth.signup');

    $this->post('/auth/signup/', 'AuthController:postSignUp');

    $this->get('/auth/login/', 'AuthController:getLogin')->setName('auth.login');

    $this->post('/auth/login/', 'AuthController:postLogin');

})->add(new Nucleus\Middleware\ACL\GuestMiddleware($container))->add(new CsrfCheckMiddleware($container));

// User routes
$app->group('', function () {

    // Authorization stuff
    $this->get('/auth/logout/', 'AuthController:getLogout')->setName('auth.logout');

    $this->get('/auth/user/update/', 'AuthController:getUpdateUser')->setName('auth.user.update');

    $this->post('/auth/user/update/', 'AuthController:postUpdateUser');

    $this->get('/auth/user/password/', 'AuthController:getPasswordChange')->setName('auth.user.password');

    $this->post('/auth/user/password/', 'AuthController:postPasswordChange');

})->add(new Nucleus\Middleware\ACL\MemberMiddleware($container))->add(new CsrfCheckMiddleware($container));

// Admin dashboard routes
$app->group('/d', function () {

    // Dashboard page
    $this->get('/', 'DashboardController:getDashboard')->setName('dashboard');
    $this->get('/dashboard/', 'DashboardController:getDashboard')->setName('dashboard');

    $this->post('/', 'DashboardController:postDashboard');

    // Global settings page
    $this->get('/settings/', 'DashboardController:getSettings')->setName('dashboard.settings');

    $this->post('/settings/', 'DashboardController:postSettings');

    // User management pages
    $this->get('/users/', 'DashboardController:getUsers')->setName('dashboard.users');

    $this->post('/user/create/', 'DashboardController:createUser')->setName('dashboard.user.create');

    $this->get('/user/{uuid}/', 'DashboardController:getUser')->setName('dashboard.user');

    $this->post('/user/{uuid}/', 'DashboardController:postUser');

    $this->post('/user/{uuid}/password/', 'DashboardController:postUserPassword')->setName('dashboard.user.password');


})->add(new Nucleus\Middleware\ACL\AdminMiddleware($container))
    ->add(new Nucleus\Middleware\ACL\MemberMiddleware($container));

/**
 * API routes -- output JSON
 */

// User routes
$app->group('/api', function () {

    $this->post('/user/logout/', 'UserController:logout');

})->add(new Nucleus\Middleware\ACL\MemberMiddleware($container))->add(new CsrfCheckMiddleware($container));

//$app->get('/api/user/reset/', 'UserController:getResetCode');

// Admin routes
$app->group('/api', function () {

    $this->post('/user/', 'UserController:createUser');

    //remember to include header X-Http-Method-Override:PUT, actually use POST
    $this->put('/user/{uuid}/', 'UserController:updateUser');

    $this->put('/user/{uuid}/deactivate/', 'UserController:deactivateUser');

    $this->put('/user/{uuid}/activate/', 'UserController:activateUser');

    $this->delete('/user/{uuid}/', 'UserController:deleteUser')->setName('api.user.delete');

    $this->get('/user/', 'UserController:getUsers');

    $this->get('/user/{uuid}/', 'UserController:getUser');

    $this->get('/user/{uuid}/reset/send/', 'UserController:sendResetCode');

})->add(new Nucleus\Middleware\ACL\AdminMiddleware($container))
    ->add(new Nucleus\Middleware\ACL\MemberMiddleware($container));

// Authenticate routes

$app->post('/api/user/login/', 'UserController:login');

$app->get('/api/user/{uuid}/reset/', 'UserController:getResetCode');

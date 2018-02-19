<?php
/**
 * Dependency inject container (DIC) setup. To be required by bootstrap.php.
 */

use Respect\Validation\Validator as v;

$container = $app->getContainer();

/* Helper Classes */

/**
 * Manage JSON web tokens
 * @param $c
 * @return \Nucleus\Helpers\TokenManager
 */
$container['token_manager'] = function ($c) {
    return new \Nucleus\Helpers\TokenManager($c['debug.log']);
};

/**
 * Manage users
 * @param $c
 * @return \Nucleus\Helpers\UserManager
 */
$container['user_manager'] = function ($c) {
    return new \Nucleus\Helpers\UserManager($c);
};

/**
 * Manage background processes
 */
$container['background_process'] = $container->factory(function ($c) {
    return new \Nucleus\Helpers\BackgroundProcess();
});

/* Components */

/**
 * Flash message handler
 * @return \Slim\Flash\Messages
 */
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

/**
 * Twig template engine
 * @param $c
 * @return \Slim\Views\Twig
 */
$container['view'] = function ($c) {
    $env = $c->get('settings')['env'];
    $view = new \Slim\Views\Twig(realpath($env['env_path'] . '/src/View/templates'), [
        //'cache' => realpath($env['env_path'] . '/src/View/cache'),
        'auto_reload' => ( getenv('ENV') == 'development' ? true : false ),
        'strict_variables' => ( getenv('ENV') == 'development' ? false : true ),
        'debug' => true
    ]);

    // Instantiate and add Slim and Nucleus specific extensions
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c->router, $basePath));
    $view->addExtension(new Nucleus\View\DebugExtension);
    $view->addExtension(new Twig_Extension_Debug());

    // Pass in some global variables
    $view->getEnvironment()->addGlobal('env', getenv('ENV'));
    $view->getEnvironment()->addGlobal('name', getenv('NAME'));
    $view->getEnvironment()->addGlobal('base_url', getenv('BASE_URL'));
    $view->getEnvironment()->addGlobal('domain', getenv('DOMAIN'));

    if ($c->user_manager->check()) {
        $view->getEnvironment()->addGlobal('auth', [
            'check' => $c->user_manager->check(),
            'user' => $c->user_manager->currentUser(),
            'roles' => $c->user_manager->currentUser()->getRoles()
        ]);
    };

    $view->getEnvironment()->addGlobal('flash', $c->flash);

    return $view;
};

/**
 * monolog -- build multiple log handlers based upon $LOGS
 * Define new logs by adding its name to the $LOGS variable in config/.env, comma separated
 */
$logs = explode(",", getenv('LOGS'));
foreach ($logs as $log) {
    $log_name = $log . ".log";

    $container[$log_name] = function ($c) use ($log_name) {
        $env = $c->get('settings')['env'];
        $settings = $c->get('settings')['logger'];
        $path = realpath($env['env_path'] . getenv('LOGS_ROOT')) . '/' . $log_name;
        $logger = new Monolog\Logger($log_name);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($path, $settings['level']));
        return $logger;
    };
}

/**
 * Service factory for Eloquent ORM
 * @param $c
 * @return \Illuminate\Database\Capsule\Manager
 */
$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container->get('db');

/**
 * @return \Slim\Csrf\Guard
 */
$container['csrf'] = function () {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

/**
 * Respect Validator
 * @param $c
 * @return \Nucleus\Helpers\Validator
 */
$container['validator'] = function ($c) {
    return new \Nucleus\Helpers\Validator($c);
};

/**
 * Validation rules
 */
v::with('Nucleus\\Helpers\\Rules\\');

/**
 * JSON Web Token
 * @return \Firebase\JWT\JWT
 */
$container['jwt'] = function () {
    return new Firebase\JWT\JWT();
};

/**
 * UUID generator
 * @return \Ramsey\Uuid\UuidInterface
 */
$container['uuid'] = function () {
    return Ramsey\Uuid\Uuid::uuid4();
    //return Ramsey\Uuid\Uuid;
};

/**
 * Event Emitter Handler
 * @return League\Event\Emitter
 */
$container['emitter'] = function () {
    return new League\Event\Emitter;
};

/**
 * CLImate -- CLI return formatting
 * @return \League\CLImate\CLImate
 */
$container['cli'] = function () {
    return new League\CLImate\CLImate;
};

/**
 * Whoops -- error handling
 */
$container['phpErrorHandler'] = $container['errorHandler'] = function ($container) {
    $logger = $container['error.log'];
    $whoopsHandler = new Dopesong\Slim\Error\Whoops();

    $whoopsHandler->pushHandler(
        function ($exception) use ($logger) {
            /** @var \Exception $exception */
            $logger->error($exception->getMessage(), ['exception' => $exception]);
            return Whoops\Handler\Handler::DONE;
        }
    );

    return $whoopsHandler;
};

/* Listener Classes */

/**
 * Register a test listener
 * @return \Nucleus\Helpers\Listeners\TestListener
 */
$container['listener.test'] = function () {
    return new \Nucleus\Helpers\Listeners\TestListener();
};

/* Controller Classes */

/**
 * Home page controller
 * @param $c
 * @return \Nucleus\Controllers\HomeController
 */
$container['HomeController'] = function ($c) {
    $controller = new \Nucleus\Controllers\HomeController($c);
    return $controller;
};

/**
 * Controller for API based user routes
 * @param $c
 * @return \Nucleus\Controllers\UserController
 */
$container['UserController'] = function ($c) {
    $controller = new \Nucleus\Controllers\UserController($c);
    return $controller;
};

/**
 * Controller for interface based user routes
 * @param $c
 * @return \Nucleus\Controllers\AuthController
 */
$container['AuthController'] = function ($c) {
    $controller = new \Nucleus\Controllers\AuthController($c);
    return $controller;
};

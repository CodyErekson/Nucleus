<?php
// DIC configuration

use Respect\Validation\Validator as v;

$container = $app->getContainer();

// Start with authentication
$container['auth'] = function () {
	return new \Nucleus\Helpers\Auth();
};

$container['flash'] = function () {
	return new \Slim\Flash\Messages();
};

// Register component on container
$container['view'] = function ($c) {
	$env = $c->get('settings')['env'];
	$view = new \Slim\Views\Twig(realpath($env['env_path'] . '/src/View/templates'), [
		//'cache' => realpath($env['env_path'] . '/src/View/cache')
		//'debug' => true
	]);

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($c->router, $basePath));
	//$view->addExtension(new \Twig_Extension_Debug);
	$view->addExtension(new Nucleus\View\DebugExtension);

	$view->getEnvironment()->addGlobal('auth', [
		'check' => $c->auth->check(),
		'user' => $c->auth->user()
	]);

	$view->getEnvironment()->addGlobal('flash', $c->flash);

	return $view;
};

// monolog -- build multiple log handlers based upon $LOGS
$logs = explode(",", getenv('LOGS'));
foreach($logs as $log) {
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

// Service factory for the ORM
$container['db'] = function ($c) {
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($c['settings']['db']);

	$capsule->setAsGlobal();
	$capsule->bootEloquent();

	return $capsule;
};

$container->get('db');

$container['csrf'] = function () {
	return new \Slim\Csrf\Guard();
};

$container['validator'] = function () {
	return new \Nucleus\Helpers\Validator();
};

$container['jwt'] = function () {
	return new Firebase\JWT\JWT();
};

$container['uuid'] = function () {
	return Ramsey\Uuid\Uuid::uuid4();
};

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

// Helper Classes
$container['token_manager'] = function ($c) {
	return new \Nucleus\Helpers\TokenManager($c['debug.log']);
};

$container['user_manager'] = function ($c) {
	return new \Nucleus\Helpers\UserManager($c['debug.log']);
};

v::with('Nucleus\\Helpers\\Rules\\');

// Controller Classes
$container['HomeController'] = function($c) {
	$controller = new \Nucleus\Controllers\HomeController($c);
	return $controller;
};

$container['UserController'] = function($c) {
	$controller = new \Nucleus\Controllers\UserController($c);
	return $controller;
};

$container['AuthController'] = function($c) {
	$controller = new \Nucleus\Controllers\AuthController($c);
	return $controller;
};
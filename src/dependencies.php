<?php
// DIC configuration

$container = $app->getContainer();

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

$container['validator'] = function ($c) {
	return new \Nucleus\Helpers\Validator();
};

$container['jwt'] = function ($c) {
	return new Firebase\JWT\JWT();
};

$container['uuid'] = function ($c) {
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
	return new Nucleus\Helpers\TokenManager($c['debug.log']);
};

$container['user_manager'] = function ($c) {
	return new Nucleus\Helpers\UserManager($c['debug.log']);
};


// Controller Classes
$container['HomeController'] = function($c) {
	$controller = new Nucleus\Controllers\HomeController($c);
	return $controller;
};

$container['UserController'] = function($c) {
	$controller = new Nucleus\Controllers\UserController($c);
	return $controller;
};

$container['AuthController'] = function($c) {
	$controller = new Nucleus\Controllers\Auth\AuthController($c);
	return $controller;
};
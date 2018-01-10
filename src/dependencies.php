<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
	$env = $c->get('settings')['env'];
	return new Slim\Views\PhpRenderer(realpath($env['env_path'] . getenv('TEMPLATE_ROOT') . '/'));
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

$container['jwt'] = function ($c) {
	return new Firebase\JWT\JWT();
};

$container['uuid'] = function ($c) {
	return Ramsey\Uuid\Uuid::uuid4();
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
	$hc = new Nucleus\Controllers\HomeController($c->get("renderer"));
	$logs = explode(",", getenv('LOGS'));
	foreach($logs as $log) {
		$log_name = $log . ".log";
		$hc->addLogger($c[$log_name], $log_name);
	}
	return $hc;
};

$container['UserController'] = function($c) {
	$hc = new Nucleus\Controllers\UserController($c->get("renderer"), $c->user_manager, $c->token_manager, $c->jwt, $c->uuid);
	$logs = explode(",", getenv('LOGS'));
	foreach($logs as $log) {
		$log_name = $log . ".log";
		$hc->addLogger($c[$log_name], $log_name);
	}
	return $hc;
};
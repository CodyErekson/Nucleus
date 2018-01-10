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

$container['token_manager'] = function ($c) {
	return new Nucleus\Helpers\TokenManager($c['debug.log']);
};
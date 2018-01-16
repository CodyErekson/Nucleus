<?php

return [
	'settings' => [
		'displayErrorDetails' => true, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header
		'determineRouteBeforeAppMiddleware' => true,

		// .env settings
		'env' => [
			'env_path' => realpath(__DIR__ . '/../')
		],

		// Monolog settings
		'logger' => [
			'name' => 'nucleus',
			'level' => getenv('LOG_LEVEL')
		],

		// DB settings
		'db' => [
			'driver' => getenv('DB_ADAPTER'),
        	'host' => getenv('DB_HOST'),
        	'database' => getenv('DB_DATABASE'),
        	'username' => getenv('DB_USERNAME'),
        	'password' => getenv('DB_PASSWORD'),
        	'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => ''
		],
	],
];
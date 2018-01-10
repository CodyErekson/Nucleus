<?php

require 'src/bootstrap.php';

return [
	'paths' => [
		'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
    	'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
	],
	'environments' => [
    	'default_migration_table' => 'phinxlog',
    	'default_database' => 'development',
    	'production' => [
        	'adapter' => getenv('DB_ADAPTER'),
        	'host' => getenv('DB_HOST'),
        	'name' => getenv('DB_DATABASE'),
        	'user' => getenv('DB_USERNAME'),
        	'pass' => getenv('DB_PASSWORD'),
        	'port' => '3306',
        	'charset' => 'utf8'
		],
    	'development' => [
			'adapter' => getenv('DB_ADAPTER'),
			'host' => getenv('DB_HOST'),
			'name' => getenv('DB_DATABASE'),
			'user' => getenv('DB_USERNAME'),
			'pass' => getenv('DB_PASSWORD'),
        	'port' => '3306',
        	'charset' => 'utf8'
		],
	],
	'version_order' => 'creation'
];
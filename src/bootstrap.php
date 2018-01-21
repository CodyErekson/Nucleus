<?php
/**
 * Initialization script
 */

use Slim\App;
use \Firebase\JWT\JWT;
use \dBug\dBug;

if (PHP_SAPI == 'cli-server') {
	// To help the built-in PHP dev server, check if the request was actually for
	// something which should probably be served as a static file
	$url  = parse_url($_SERVER['REQUEST_URI']);
	$file = __DIR__ . $url['path'];
	if (is_file($file)) {
		return false;
	}
}

/**
 * Include dependencies via Composer
 */
require(__DIR__ . '/../vendor/autoload.php');

/**
 * Fetch our global functions
 */
require(__DIR__ . '/../src/functions.php');

/**
 * Start a session -- though not used when authenticating with JWT, it is needed for general user authentication
 */
session_start();

/**
 * Get local environment variables
 */
$env = new \Dotenv\Dotenv(realpath(__DIR__ . '/../config'));
$env->load();

/**
 * Instantiate Slim framework app
 */
$settings = require(__DIR__ . '/../src/settings.php');
$app = new \Slim\App($settings);

/**
 * Set up dependencies
 */
require(__DIR__ . '/../src/dependencies.php');

/**
 * Register middleware
 */
require(__DIR__ . '/../src/middleware.php');

/**
 * Register routes
 */
require(__DIR__ . '/../src/routes.php');

/**
 * Initialize the application
 */
$app->run();


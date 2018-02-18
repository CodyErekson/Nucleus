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
 * Include definitions
 */
require(__DIR__ . '/definitions.php');

/**
 * Include dependencies via Composer
 */
require(__DIR__ . '/../vendor/autoload.php');

/**
 * Fetch our global functions
 */
require(__DIR__ . '/functions.php');

/**
 * Start a session -- though not used when authenticating with JWT, it is needed for general user authentication
 */
session_start();

/**
 * Get local environment variables
 */
$env = new \Dotenv\Dotenv(realpath(__DIR__ . '/../config'), ENVFILE);
$env->load();

putenv("SRC_ROOT=" . __DIR__);

/**
 * Instantiate Slim framework app
 */
$settings = require(__DIR__ . '/settings.php');
$app = new \Slim\App($settings);

/**
 * Set up dependencies
 */
require(__DIR__ . '/dependencies.php');

/**
 * Get contents of composer.json in case we need it
 */
$composer = file_get_contents(__DIR__ . '/../composer.json');
$container['composer']  = json_decode($composer, true);

/**
 * And grab our global settings from the database, store in DIC and set env vars as defined
 */
if ($container->db->schema()->hasTable('settings')) {
    $settings = \Nucleus\Models\Setting::all();
    $s = [];
    foreach ($settings as $setting) {
        $s[$setting->setting] = $setting->value;
        if ($setting->env) {
            putenv($setting->setting . "=" . $setting->value);
        }
    }
    $container['global_settings'] = $s;
}

/**
 * Register middleware
 */
require(__DIR__ . '/middleware.php');

/**
 * Register routes
 */
require(__DIR__ . '/routes.php');

/**
 * Initialize the application
 */
$app->run();

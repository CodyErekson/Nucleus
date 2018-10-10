<?php
/**
 * Define constants and the like here
 */

if ($envfile = getenv('ENV_FILE')) {
    if ( file_exists(realpath(__DIR__ . '/../config/' . $envfile))) {
        define('ENVFILE', $envfile);
    } else {
        define('ENVFILE', '.env');
    }
} else {
    define('ENVFILE', '.env');
}

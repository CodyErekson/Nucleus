<?php
/**
 * Define constants and the like here
 */

if ($envfile = getenv('ENV_FILE')) {
    define('ENVFILE', $envfile);
} else {
    define('ENVFILE', '.env');
}

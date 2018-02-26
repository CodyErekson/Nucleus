<?php
/**
 * Define application middleware. Required by bootstrap.php
 */

/**
 * Configure JSON Web Token handling
 */
$app->add(new \Slim\Middleware\JwtAuthentication([
    "secret" => base64_encode(getenv('JWT_SECRET')),
    "secure" => false,
    "cookie" => "token",
    "attribute" => "jwt",
    "path" => ["/"],
    "passthrough" => ["/favicon.ico"],  //WTH? This shouldn't need to be here
    "algorithm" => 'HS256',
    "relaxed" => ["localhost", getenv('DOMAIN')],
    "rules" => [
        new \Nucleus\Helpers\RequestSessionRule($container,
            [
                "/",
                "/api/user/login/",
                "/auth/login/",
                "/auth/signup/",
                "/api/user/{uuid}/reset/"
            ]),
        new \Slim\Middleware\JwtAuthentication\RequestPathRule()
    ],
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container['token'] = $arguments["decoded"];
        // Login user (create session) if token is valid
        $container['UserController']->setToken($container['token']);
        $container->user_manager->login($arguments['decoded']->data->user->uuid);
        if ($container->user_manager->check()) {
            $container->view->getEnvironment()->addGlobal('auth', [
                'check' => $container->user_manager->check(),
                'user' => $container->user_manager->currentUser(),
                'roles' => $container->user_manager->currentUser()->getRoles()
            ]);
        }
        $container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\n"
            . json_encode($arguments['decoded']->data->user->uuid));
    },
    "error" => function ($request, $response, $arguments) use ($container) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        $container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\n" . $data["message"]);
        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(401)
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

// Run default functionality on all CLI commands
//$app->add(new \Nucleus\Middleware\CliMiddleware($container));

// Respect Validation middleware
$app->add(new \Nucleus\Middleware\ValidationMiddleware($container));

// Define global template variables for current request
$app->add(new \Nucleus\Middleware\TemplateVariableMiddleware($container));

// Allow field data to persist between page loads
$app->add(new \Nucleus\Middleware\PersistMiddleware($container));

// Register event listeners
$app->add(new \Nucleus\Middleware\EmitterMiddleware($container));

// Manage CSRF
$app->add(new \Nucleus\Middleware\CsrfMiddleware($container));

// A middleware for enabling CORS
$app->add(new \Nucleus\Middleware\CorsMiddleware($container));

// IP filtering middlewares
if ($container->db->schema()->hasTable('settings')) {
    $ip_whitelist = explode(',', $container['global_settings']['IP_WHITELIST']);
    $ip_blacklist = explode(',', $container['global_settings']['IP_BLACKLIST']);
} else {
    $ip_whitelist = $ip_blacklist = [];
}
$app->add(new \Nucleus\Middleware\IpFilterMiddleware($container, $ip_whitelist, $ip_blacklist));
$app->add(new RKA\Middleware\IpAddress(true));

// CLI runner middleware
$app->add(\adrianfalleiro\SlimCLIRunner::class);

$app->add($container->csrf);

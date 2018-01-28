<?php
/**
 * Define application middleware. Required by bootstrap.php
 */

// CLI runner middleware
$app->add(\adrianfalleiro\SlimCLIRunner::class);

/**
 * Configure JSON Web Token handling
 */
$app->add(new \Slim\Middleware\JwtAuthentication([
    "secret" => base64_encode(getenv('JWT_SECRET')),
    "secure" => false,
    "path" => ["/api/"],
    "passthrough" => ["/api/user/login/"],
    "algorithm" => 'HS256',
    "relaxed" => ["localhost", getenv('DOMAIN')],
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container['token'] = $arguments["decoded"];
        $container['UserController']->setToken($container['token']);
    },
    "error" => function ($request, $response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

// A middleware for enabling CORS
$app->add(new \Nucleus\Middleware\CorsMiddleware($container));

// Respect Validation middleware
$app->add(new \Nucleus\Middleware\ValidationMiddleware($container));

// Allow field data to persist between page loads
$app->add(new \Nucleus\Middleware\PersistMiddleware($container));

// Register event listeners
$app->add(new \Nucleus\Middleware\EmitterMiddleware($container));

// Manage CSRF
$app->add(new \Nucleus\Middleware\CsrfMiddleware($container));

$app->add($container->csrf);

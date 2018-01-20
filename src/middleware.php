<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// JSON Web Token
$app->add(new \Slim\Middleware\JwtAuthentication([
	"secret" => base64_encode(getenv('JWT_SECRET')),
	"secure" => false,
	"path" => ["/api/"],
	"passthrough" => ["/api/user/login/"],
	"algorithm" => 'HS256',
	"relaxed" => ["localhost", "nucleus.local"],
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

$app->add(new \Nucleus\Middleware\ValidationMiddleware($container));

$app->add(new \Nucleus\Middleware\PersistMiddleware($container));

$app->add(new \Nucleus\Middleware\CsrfMiddleware($container));

$app->add($container->csrf);
<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// JSON Web Token
$app->add(new \Slim\Middleware\JwtAuthentication([
	"secret" => getenv("JWT_SECRET"),
	"secure" => false,
	"path" => ["/api/", "/test/"],
	"passthrough" => ["/api/user/login/"],
	"relaxed" => ["localhost", "nucleus.local"],
	"callback" => function ($request, $response, $arguments) use ($container) {
		$container["token"] = $arguments["decoded"];
	}
]));

// A middleware for enabling CORS
$app->add(function ($req, $res, $next) {
	$response = $next($req, $res);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
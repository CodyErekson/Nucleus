<?php

/*
 * This file is part of PSR-7 JSON Web Token Authentication middleware
 *
 * Copyright (c) 2015-2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/slim-jwt-auth
 *
 */

namespace Nucleus\Helpers;

use Psr\Http\Message\RequestInterface;

/**
 * Rule to decide by request path whether the request should be authenticated or not.
 */

class RequestSessionRule implements \Slim\Middleware\JwtAuthentication\RuleInterface
{
    /**
     * Stores all the options passed to the rule
     */
    protected $container = null;
    protected $passthrough = [];

    /**
     * Create a new rule instance
     *
     * @param \Slim\Container
     * @param array $passthrough
     * @return void
     */
    public function __construct(\Slim\Container $container = null, $passthrough = [])
    {
        $this->container = $container;
        $this->passthrough = array_merge($this->passthrough, $passthrough);
    }

    /**
     * If valid session exists, do not authenticate using token
     * @param \Psr\Http\Message\RequestInterface $request
     * @return boolean
     */
    public function __invoke(RequestInterface $request)
    {
        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\n");

        // Valid session, use it instead of token to validate
        if ($this->container->user_manager->check()) {
            return false;
        }

        // Check for token
        $this->container['debug.log']->debug("Checking token from request header");
        $headers = $request->getHeader("Authorization");
        $header = isset($headers[0]) ? $headers[0] : "";

        /* Try apache_request_headers() as last resort */
        if (empty($header) && function_exists("apache_request_headers")) {
            $this->container['debug.log']->debug("Checking token from apache_request_headers()");
            $headers = apache_request_headers();
            $header = isset($headers["Authorization"]) ? $headers["Authorization"] : "";
        }

        if (preg_match("/Bearer\s+(.*)$/i", $header, $matches)) {
            $this->container['debug.log']->debug("TOKEN: " . $matches[1]);
            return true;
        }

        /* Bearer not found, try a cookie. */
        $cookie_params = $request->getCookieParams();

        if (isset($cookie_params['token'])) {
            $this->container['debug.log']->debug("Using token from cookie");
            return true;
        };

        // If we get here, we know there is no session and no token, so now check passthrough status

        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);

        $this->container['debug.log']->debug("No token, checking if " . $uri . " is in passthrough");

        // If we have a passthrough path
        foreach ((array)$this->passthrough as $passthrough) {
            preg_match_all("/{(.*?)}/", $passthrough, $matches);
            foreach ($matches[1] as $match) {
                if (strpos($passthrough, "{" . $match . "}") !== false) {
                    $route = $request->getAttribute('route');
                    if (!is_null($route)) {
                        $arg = $route->getArgument($match);
                        $passthrough = str_replace("{" . $match . "}", $arg, $passthrough);
                    }
                }
            }
            $this->container['debug.log']->debug("Compare URI " . $uri . " to passthrough " . $passthrough);
            if (strcmp($uri, $passthrough) == 0) {
                return false;
            }
        }

        return true;
    }
}

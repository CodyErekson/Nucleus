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

    /**
     * Create a new rule instance
     *
     * @param \Slim\Container
     * @return void
     */
    public function __construct(\Slim\Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * If valid session exists, do not authenticate using token
     * @param \Psr\Http\Message\RequestInterface $request
     * @return boolean
     */
    public function __invoke(RequestInterface $request)
    {
        if ($this->container->user_manager->check()) {
            return false;
        }
        return true;
    }
}

<?php
/**
 * Create some template variables based upon current request
 */

namespace Nucleus\Middleware;

use Respect\Validation\Validator as v;

/**
 * Class IpFilterMiddleware
 * @package Nucleus\Middleware
 */
class IpFilterMiddleware extends BaseMiddleware
{

    protected $whitelist = [];
    protected $blacklist = [];

    /**
     * Create new RestrictRoute service provider.
     *
     * @param $container
     * @param array $whitelist
     * @param array $blacklist
     */
    public function __construct($container, array $whitelist = [], array $blacklist = [])
    {
        $this->whitelist = array_merge($this->whitelist, $whitelist);
        $this->blacklist = array_merge($this->blacklist, $blacklist);
        parent::__construct($container);
    }

    /**
     * RestrictRoute middleware invokable class.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        // First make sure the reported IP address is valid
        $ipAddress = $request->getAttribute('ip_address');
        try {
            if (!v::ip()->validate($ipAddress)) {
                return $response->withStatus(401)->withJson(["error" => "Invalid IP address"]);
            }
        } catch (\Exception $e) {
            return $response->withStatus(403)->withJson(["error" => $e->getMessage() . " on line " . __LINE__]);
        }
        // Now we make sure all of the IPs and ranges in the whitelist and blacklist are valid
        $lists = array_merge($this->whitelist, $this->blacklist);
        if (!empty($lists)) {
            foreach ($lists as $ip) {
                if (strlen($ip) <= 0) {
                    continue;
                }
                $range = false;
                if (strpos($ip, '-') !== false) {
                    $range = $ip;
                } elseif (strpos($ip, '*') !== false) {
                    $range = $ip;
                } elseif (strpos($ip, '/') !== false) {
                    $range = $ip;
                }
                try {
                    if ($range) {
                        if (!v::ip($range)) {
                            return $response->withStatus(401)->withJson(["error" => "Invalid IP address"]);
                        }
                    } else { //ip
                        if (!v::ip()->validate($ip)) {
                            return $response->withStatus(401)->withJson(["error" => "Invalid IP address"]);
                        }
                    }
                } catch (\Exception $e) {
                    return $response->withStatus(403)->withJson(["error" => $e->getMessage() . " on line " . __LINE__]);
                }
            }
        }

        // Finally see if current IP is in the whitelist and blacklist arrays
        if (!empty($this->blacklist)) {
            try {
                if (v::contains($ipAddress)->validate($this->blacklist)) {
                    return $response->withStatus(401)->withJson(["error" => "IP address is in the blacklist"]);
                }
                return $next($request, $response);
            } catch (\Exception $e) {
                return $response->withStatus(403)->withJson(["error" => $e->getMessage() . " on line " . __LINE__]);
            }
        }
        if (!empty($this->whitelist)) {
            try {
                if (!v::contains($ipAddress)->validate($this->whitelist)) {
                    return $response->withStatus(401)->withJson(["error" => "IP address is not in whitelist"]);
                }
                return $next($request, $response);
            } catch (\Exception $e) {
                return $response->withStatus(403)->withJson(["error" => $e->getMessage() . " on line " . __LINE__]);
            }
        }
    }

    /**
     * Get the whitelist array.
     *
     * @return array
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * Set the options array.
     *
     * @param array $whitelist The ips array.
     */
    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
    }

    /**
     * Get the blacklist array.
     *
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * Set the options array.
     *
     * @param array $blacklist The ips array.
     */
    public function setBlacklist($blacklist)
    {
        $this->blacklist = $blacklist;
    }
}

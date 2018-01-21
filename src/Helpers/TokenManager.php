<?php
/**
 * Helper functions for JSON Web Token management
 */

namespace Nucleus\Helpers;

/**
 * Class TokenManager
 * @package Nucleus\Helpers
 * @param \Slim\Container $container
 */
class TokenManager {

	private $container = null;
	private $uuid = null;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	public function setUserId($uuid)
	{
		$this->uuid = $uuid;
	}

	public function cleanExpired()
	{
		$tokens = \Nucleus\Models\Token::where('uuid', '=', $this->uuid)
			->where('expiration', '<', date('Y-m-d H:i:s'))
			->get();

		$tokens->each(function ($token) {
			$this->container['debug.log']->debug('Deleting token:', $token->toArray());
			$token->delete();
		});
	}

	public function flush()
	{
		$tokens = \Nucleus\Models\Token::where('uuid', '=', $this->uuid)->get();

		$tokens->each(function ($token) {
			$this->container['debug.log']->debug('Deleting token:', $token->toArray());
			$token->delete();
		});
	}

}
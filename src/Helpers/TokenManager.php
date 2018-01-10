<?php

namespace Nucleus\Helpers;

class TokenManager {

	private $logger = null;
	private $uuid = null;

	public function __construct(\Monolog\Logger $logger)
	{
		$this->logger = $logger;
	}

	public function setUserId($uuid)
	{
		$this->$uuid = $uuid;
	}

	public function cleanExpired()
	{
		$tokens = \Nucleus\Models\Token::where('uuid', '=', $this->uuid)
			->where('expiration', '<', date('Y-m-d H:i:s'))
			->get();

		$tokens->each(function ($token) {
			$this->logger->debug('Deleting token:', $token->toArray());
			$token->delete();
		});
	}

	public function flush()
	{
		$tokens = \Nucleus\Models\Token::where('uuid', '=', $this->uuid)->get();

		$tokens->each(function ($token) {
			$this->logger->debug('Deleting token:', $token->toArray());
			$token->delete();
		});
	}

}
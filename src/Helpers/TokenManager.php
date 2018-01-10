<?php

namespace Nucleus\Helpers;

class TokenManager {

	private $logger = null;
	private $user_id = null;

	public function __construct(\Monolog\Logger $logger)
	{
		$this->logger = $logger;
	}

	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	public function cleanExpired()
	{
		$tokens = \Nucleus\Models\Token::where('user_id', '=', $this->user_id)
			->where('expiration', '<', date('Y-m-d H:i:s'))
			->get();

		$tokens->each(function ($token) {
			$this->logger->debug('Deleting token:', $token->toArray());
			$token->delete();
		});
	}

	public function flush()
	{
		$tokens = \Nucleus\Models\Token::where('user_id', '=', $this->user_id)->get();

		$tokens->each(function ($token) {
			$this->logger->debug('Deleting token:', $token->toArray());
			$token->delete();
		});
	}

}
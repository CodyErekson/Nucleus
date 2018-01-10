<?php

namespace Nucleus\Helpers;

class UserManager {

	private $logger = null;
	private $uuid = null;

	public function __construct(\Monolog\Logger $logger)
	{
		$this->logger = $logger;
	}

	public function setUserId($uuid)
	{
		$this->uuid = $uuid;
	}

	public function validateUsernameUnique($username)
	{
		$users = \Nucleus\Models\User::where('username', '=', $username)
			->where('active', '=', true)
			->get();

		$this->logger->debug('Users found:', $users->toArray());
		if ( count($users) ){
			return false;
		} else {
			return true;
		}
	}

	public function validateEmailUnique($email)
	{
		$users = \Nucleus\Models\User::where('email', '=', $email)
			->where('active', '=', true)
			->get();

		$this->logger->debug('Users found:', $users->toArray());
		if ( count($users) ){
			return false;
		} else {
			return true;
		}
	}
}
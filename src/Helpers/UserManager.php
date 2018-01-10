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

	public function validateUsernameUnique($username, $uuid=null)
	{
		if ( is_null($uuid) ) {
			$users = \Nucleus\Models\User::where('username', '=', $username)
				->where('active', '=', true)
				->get();
		} else {
			$users = \Nucleus\Models\User::where('username', '=', $username)
				->where('uuid', '!=', $uuid)
				->where('active', '=', true)
				->get();
		}

		$this->logger->debug('Users found by username:', $users->toArray());
		if ( count($users) ){
			return false;
		} else {
			return true;
		}
	}

	public function validateEmailUnique($email, $uuid=null)
	{
		if ( is_null($uuid) ) {
			$users = \Nucleus\Models\User::where('email', '=', $email)
				->where('active', '=', true)
				->get();
		} else {
			$users = \Nucleus\Models\User::where('email', '=', $email)
				->where('uuid', '!=', $uuid)
				->where('active', '=', true)
				->get();
		}

		$this->logger->debug('Users found by email:', $users->toArray());
		if ( count($users) ){
			return false;
		} else {
			return true;
		}
	}
}
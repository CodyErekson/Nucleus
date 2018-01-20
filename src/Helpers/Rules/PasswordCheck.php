<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class PasswordCheck extends AbstractRule
{

	protected $username;
	protected $uuid;

	public function __construct($username=null, $uuid=null)
	{
		$this->username = $username;
		$this->uuid = $uuid;
	}

	public function validate($input)
	{
		if ( !is_null($this->uuid) ) {
			$user = User::where('uuid', $this->uuid)->first();
		} else {
			$user = User::where('username', $this->username)->first();
		}
		if ( !$user ){
			return false;
		}
		if ( password_verify($input, $user->password) ){
			return true;
		}
		return false;
	}
}
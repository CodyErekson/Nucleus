<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class EmailAvailable extends AbstractRule
{
	protected $uuid = null;

	public function __construct($uuid=null)
	{
		$this->uuid = $uuid;
	}

	public function validate($input)
	{
		if ( is_null($this->uuid) ) { //checking for new user
			return User::where('email', $input)->count() === 0;
		} else { //checking for existing user, exclude current
			return User::where('email', $input)-> where('uuid', '!=', $this->uuid)->count() === 0;
		}
	}
}
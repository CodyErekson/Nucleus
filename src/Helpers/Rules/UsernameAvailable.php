<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class UsernameAvailable extends AbstractRule
{

	public function validate($input)
	{
		return User::where('username', $input)->count() === 0;
	}
}
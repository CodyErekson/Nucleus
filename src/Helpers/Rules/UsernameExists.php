<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class UsernameExists extends AbstractRule
{

	public function validate($input)
	{
		return User::where('username', $input)->where('active', '=', true)->count() === 1;
	}
}
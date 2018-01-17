<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class EmailAvailable extends AbstractRule
{

	public function validate($input)
	{
		return User::where('email', $input)->count() === 0;
	}
}
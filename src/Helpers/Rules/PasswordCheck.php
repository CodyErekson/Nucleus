<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class PasswordCheck extends AbstractRule
{

	public function validate($password)
	{
		if ( !isset($_SESSION['uuid']) ){
			return false;
		}
		$user = User::where('uuid', $_SESSION['uuid']);
		if ( password_verify($password, $user->password) ){
			return true;
		}

	}
}
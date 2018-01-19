<?php

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

class ConfirmPassword extends AbstractRule
{

	protected $password;

	public function __construct($password)
	{
		$this->password = $password;
	}

	public function validate($input)
	{
		if ( $input === $this->password ){
			return true;
		}
		return false;
	}
}
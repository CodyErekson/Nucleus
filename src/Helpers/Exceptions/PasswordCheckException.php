<?php

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class PasswordCheckException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Password is incorrect.'
		],
	];
}
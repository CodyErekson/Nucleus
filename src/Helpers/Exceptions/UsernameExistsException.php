<?php

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class UsernameExistsException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Username does not exist.'
		],
	];
}
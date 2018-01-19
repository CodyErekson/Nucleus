<?php

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ConfirmPasswordException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Password confirmation does not match.'
		],
	];
}
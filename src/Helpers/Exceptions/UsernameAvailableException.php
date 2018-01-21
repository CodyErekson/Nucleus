<?php
/**
 * Exception handler for UsernameAvailable validation rule
 */

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class UsernameAvailableException
 * @package Nucleus\Helpers\Exceptions
 */
class UsernameAvailableException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Username is not available.'
		],
	];
}
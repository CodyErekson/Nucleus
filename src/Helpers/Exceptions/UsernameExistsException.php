<?php
/**
 * Exception handler for UsernameExists validation rule
 */

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class UsernameExistsException
 * @package Nucleus\Helpers\Exceptions
 */
class UsernameExistsException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Username does not exist.'
		],
	];
}
<?php
/**
 * Exception handler for ConfirmPassword validation rule
 */

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class ConfirmPasswordException
 * @package Nucleus\Helpers\Exceptions
 */
class ConfirmPasswordException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Password confirmation does not match.'
		],
	];
}
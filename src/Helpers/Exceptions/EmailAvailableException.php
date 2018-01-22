<?php
/**
 * Exception handler for EmailAvailable validation rule
 */

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class EmailAvailableException
 * @package Nucleus\Helpers\Exceptions
 */
class EmailAvailableException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Email is already in use.'
		],
	];
}
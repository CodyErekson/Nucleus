<?php
/**
 * Exception handler for PasswordCheck validation rule
 */

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class PasswordCheckException
 * @package Nucleus\Helpers\Exceptions
 */
class PasswordCheckException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Password is incorrect.'
        ],
    ];
}

<?php
/**
 * Exception handler for ConfirmPassword validation rule
 */

namespace Nucleus\Helpers\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class ResetCodeException
 * @package Nucleus\Helpers\Exceptions
 */
class ResetCodeException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Password reset code is not valid'
        ],
    ];
}

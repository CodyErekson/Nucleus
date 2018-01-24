<?php
/**
 * Respect Validation rule to compare a new password with the string in the "confirm password" field
 */

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class ConfirmPassword
 * @package Nucleus\Helpers\Rules
 */
class ConfirmPassword extends AbstractRule
{

    protected $password;

    /**
     * ConfirmPassword constructor.
     * @param $password
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        if ($input === $this->password) {
            return true;
        }
        return false;
    }
}

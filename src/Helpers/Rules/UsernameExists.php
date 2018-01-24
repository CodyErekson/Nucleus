<?php
/**
 * Respect Validation rule to ensure provided username exists
 */

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class UsernameExists
 * @package Nucleus\Helpers\Rules
 */
class UsernameExists extends AbstractRule
{

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        return User::where('username', $input)->where('active', '=', true)->count() === 1;
    }
}

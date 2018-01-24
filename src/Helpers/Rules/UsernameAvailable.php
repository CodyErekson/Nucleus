<?php
/**
 * Respect Validation rule to ensure provided username does not already exist
 */

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class UsernameAvailable
 * @package Nucleus\Helpers\Rules
 */
class UsernameAvailable extends AbstractRule
{
    protected $uuid = null;

    /**
     * UsernameAvailable constructor
     * @param null $uuid
     */
    public function __construct($uuid = null)
    {
        $this->uuid = $uuid;
    }

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        if (is_null($this->uuid)) { //checking for new user
            return User::where('username', $input)->count() === 0;
        } else { //checking for existing user, exclude current
            return User::where('username', $input)-> where('uuid', '!=', $this->uuid)->count() === 0;
        }
    }
}

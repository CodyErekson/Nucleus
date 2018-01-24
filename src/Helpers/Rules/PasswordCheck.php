<?php
/**
 * Respect Validation rule to ensure provided password is correct for given user
 */

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class PasswordCheck
 * @package Nucleus\Helpers\Rules
 */
class PasswordCheck extends AbstractRule
{

    protected $username;
    protected $uuid;

    /**
     * PasswordCheck constructor -- validation requires either username or uuid
     * @param null $username
     * @param null $uuid
     */
    public function __construct($username = null, $uuid = null)
    {
        $this->username = $username;
        $this->uuid = $uuid;
    }

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        if (!is_null($this->uuid)) {
            $user = User::where('uuid', $this->uuid)->first();
        } else {
            $user = User::where('username', $this->username)->first();
        }
        if (!$user) {
            return false;
        }
        if (password_verify($input, $user->password)) {
            return true;
        }
        return false;
    }
}

<?php
/**
 * Respect Validation rule to compare a new password with the string in the "confirm password" field
 */

namespace Nucleus\Helpers\Rules;

use Nucleus\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class ResetCode
 * @package Nucleus\Helpers\Rules
 */
class ResetCode extends AbstractRule
{

    protected $username;

    /**
     * ConfirmPassword constructor.
     * @param $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * Make sure the reset code belongs to the given user and is valid
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        $input = base64_encode(hex2bin($input));
        $code = \Nucleus\Models\ResetCode::where('code', $input)->first();
        if ( is_null($code) ){
            return false;
        }

        if ( !$code->isValid() ){
            return false;
        }

        $code_user = \Nucleus\Models\User::where('uuid', $code->uuid)->where('active', true)->first();
        if ( is_null($code_user) ){
            return false;
        }

        $user = \Nucleus\Models\User::where('username', $this->username)->where('active', true)->first();
        if ( is_null($user) ){
            return false;
        }

        if ( $code_user->is($user) ){
            return true;
        }
        return false;
    }
}

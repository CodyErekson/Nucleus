<?php
/**
 * Helper functions for user management
 */

namespace Nucleus\Helpers;

use Nucleus\Models\User;
use Respect\Validation\Validator as v;

/**
 * Class UserManager
 * @package Nucleus\Helpers
 */
class UserManager
{

    private $container = null;
    private $uuid = null;
    private $user = null;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }

    /**
     * Fetch the currently logged-in user
     * @return User|null
     */
    public function currentUser()
    {
        if (( is_null($this->user) ) || ( $this->user->uuid != $_SESSION['uuid'] )) {
            if (isset($_SESSION['uuid'])) {
                $this->user = User::find($_SESSION['uuid']);
            } else {
                return null;
            }
        }
        return $this->user;
    }

    /**
     * Check if user is currently logged in
     * @return bool
     */
    public function check()
    {
        return isset($_SESSION['uuid']);
    }

    /**
     * Assign a UUID to a user
     * @param $uuid
     */
    public function setUserId($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Validation required to create a new user
     * @param $request
     * @return bool
     */
    public function createUserValidation($request)
    {
        $validation = $this->container->validator->validate($request, [
            'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable(),
            'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable(),
            'password' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
            'confirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                ->confirmPassword($request->getParam('password')),
        ]);

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Create a new user based upon submitted data
     * @param $request
     * @return mixed
     */
    public function createUser($request)
    {
        $user = User::create([
            'uuid' => $this->container->uuid->toString(),
            'username' => $request->getParam('username'),
            'email' => $request->getParam('email'),
            'password' => password_hash($request->getParam('password'), PASSWORD_BCRYPT)
        ]);

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nNew user: ", $user->toArray());

        $role = \Nucleus\Models\Role::find('2');
        $user->roles()->save($role);
        return $user;
    }

    /**
     * Validate allowed user details based upon submitted data
     * @param $request
     * @param $uuid
     * @return bool
     */
    public function updateUserValidation($request, $uuid)
    {
        $validation = $this->container->validator->validate($request, [
            'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable($uuid),
            'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable($uuid),
        ]);

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Validate any user details based upon submitted data
     * @param $request
     * @param $uuid
     * @return bool
     */
    public function updateUserValidationAdmin($request, $uuid)
    {
        $validation = $this->container->validator->validate($request, [
            'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable($uuid),
            'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable($uuid),
            'password' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
            'confirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                ->confirmPassword($request->getParam('password')),
        ]);

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Update the validated user details based upon submitted data
     * @param $data
     * @param $uuid
     * @return mixed
     */
    public function updateUser($data, $uuid)
    {
        $user = \Nucleus\Models\User::find($uuid);

        $user->update($data);

        return $user;
    }

    /**
     * Validate submitted password
     * @param $request
     * @return bool
     */
    public function changePasswordValidation($request)
    {
        $validation = $this->container->validator->validate($request, [
            'current' => v::notEmpty()->length(8)->noWhitespace()
                ->passwordCheck(false, $_SESSION['uuid']),
            'password' => v::notEmpty()->length(8)->noWhitespace(),
            'confirm' => v::notEmpty()->length(8)->noWhitespace()
                ->confirmPassword($request->getParam('password')),
        ]);

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Valid supplied user login credentials
     * @param $request
     * @return bool
     */
    public function loginValidation($request)
    {
        $validation = $this->container->validator->validate($request, [
            'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()
                ->usernameExists(),
            'password' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                ->passwordCheck($request->getParam('username')),
        ]);

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Set session UUID parameter and create cookie containing user's JSON Web Token
     * @param $uuid
     */
    public function login($uuid)
    {
        $_SESSION['uuid'] = $uuid;
        $this->currentUser()->setContainer($this->container);
        $token = $this->currentUser()->getToken();
        setcookie('token', $token->token, time() + (3600 * 24 * 15), '/', getenv('DOMAIN'));
    }

    /**
     * Destroy current session and cookie
     */
    public function logout()
    {
        if (isset($_SESSION['uuid'])) {
            unset($_SESSION['uuid']);
            session_destroy();
            setcookie('token', '', time() - 3600, '/', getenv('DOMAIN'));
        }
    }

    /**
     * Set a user to enabled or disabled
     * @param $uuid
     * @param bool $state
     * @return User
     */
    public function setActive($uuid, $state = true)
    {
        $user = \Nucleus\Models\User::find($uuid);
        $user->setActive($state);
        $user->save();
        return $user;
    }

    /**
     * Entirely remove the given user from the database
     * @param $uuid
     * @return mixed
     */
    public function deleteUser($uuid)
    {
        $user = \Nucleus\Models\User::find($uuid);
        return $user->delete();
    }
}

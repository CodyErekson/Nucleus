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
     * Check if user is an admin
     * @return bool
     */
    public function adminCheck()
    {
        if (!$this->check()) {
            return false;
        }
        return $this->container->user_manager->currentUser()->getAdmin();
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
        if (is_array($request)) {
            $validation = $this->container->validator->validateArray($request, [
                'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable(),
                'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable(),
                'password' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                    ->confirmPassword($request['password']),
            ]);
        } else {
            $validation = $this->container->validator->validate($request, [
                'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable(),
                'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable(),
                'password' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                    ->confirmPassword($request->getParam('password')),
            ]);
        }

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
        if (is_array($request)) {
            $user = User::create([
                'uuid' => $this->container->uuid->toString(),
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => password_hash($request['password'], PASSWORD_BCRYPT)
            ]);
        } else {
            $user = User::create([
                'uuid' => $this->container->uuid->toString(),
                'username' => $request->getParam('username'),
                'email' => $request->getParam('email'),
                'password' => password_hash($request->getParam('password'), PASSWORD_BCRYPT)
            ]);
        }

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nNew user: ", $user->toArray());

        $role = \Nucleus\Models\Role::where("role", "=", "member")->first();
        $user->addRole($role->id);

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
        if (is_array($request)) {
            $validation = $this->container->validator->validateArray($request, [
                'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable($uuid),
                'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable($uuid),
            ]);
        } else {
            $validation = $this->container->validator->validate($request, [
                'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable($uuid),
                'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable($uuid),
            ]);
        }

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
        if (is_array($request)) {
            $validation = $this->container->validator->validateArray($request, [
                'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable($uuid),
                'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable($uuid),
                'password' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                    ->confirmPassword($request['password']),
            ]);
        } else {
            $validation = $this->container->validator->validate($request, [
                'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameAvailable($uuid),
                'email' => v::notEmpty()->noWhitespace()->email()->emailAvailable($uuid),
                'password' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                    ->confirmPassword($request->getParam('password')),
            ]);
        }

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
     * Change the password for the given user, assume that validation has already occurred
     * @param $password
     * @param $uuid
     * @return mixed
     */
    public function changePassword($password, $uuid)
    {
        $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        return $this->updateUser($data, $uuid);
    }

    /**
     * Validate submitted password
     * @param $request
     * @return bool
     */
    public function changePasswordValidation($request)
    {
        if (is_array($request)) {
            $validation = $this->container->validator->validateArray($request, [
                'current' => v::notEmpty()->length(8)->noWhitespace()
                    ->passwordCheck(false, $_SESSION['uuid']),
                'password' => v::notEmpty()->length(8)->noWhitespace(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()
                    ->confirmPassword($request['password']),
            ]);
        } else {
            $validation = $this->container->validator->validate($request, [
                'current' => v::notEmpty()->length(8)->noWhitespace()
                    ->passwordCheck(false, $_SESSION['uuid']),
                'password' => v::notEmpty()->length(8)->noWhitespace(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()
                    ->confirmPassword($request->getParam('password')),
            ]);
        }

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Validate submitted password without providing current
     * @param $request
     * @return bool
     */
    public function changePasswordValidationAdmin($request)
    {
        if (is_array($request)) {
            $validation = $this->container->validator->validateArray($request, [
                'password' => v::notEmpty()->length(8)->noWhitespace(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()
                    ->confirmPassword($request['password']),
            ]);
        } else {
            $validation = $this->container->validator->validate($request, [
                'password' => v::notEmpty()->length(8)->noWhitespace(),
                'confirm' => v::notEmpty()->length(8)->noWhitespace()
                    ->confirmPassword($request->getParam('password')),
            ]);
        }

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Validate supplied password reset data
     * @param $request
     * @return bool
     */
    public function resetCodeValidation($request)
    {
        $validation = $this->container->validator->validate($request, [
            'cpusername' => v::notEmpty()->length(4, 20)->stringType()->alnum()
                ->usernameExists(),
            'cpresetcode' => v::notEmpty()->resetCode($request->getParam('cpusername')),
            'cppassword' => v::notEmpty()->length(8)->noWhitespace()->stringType(),
            'cpconfirm' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                ->confirmPassword($request->getParam('cppassword')),
        ]);

        if ($validation->failed()) {
            return false;
        }
        return true;
    }

    /**
     * Validate supplied user login credentials
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
        try {
            $_SESSION['uuid'] = $uuid;
            $this->currentUser()->setContainer($this->container);
            $token = $this->currentUser()->getToken();
            setcookie('token', $token->token, time() + (3600 * 24 * 15), '/', getenv('DOMAIN'));
        } catch (\Exception $e) {
            $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\n" . $e->getMessage());
        }
    }

    /**
     * Destroy current session and cookie
     */
    public function logout()
    {
        $this->currentUser()->logout();
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

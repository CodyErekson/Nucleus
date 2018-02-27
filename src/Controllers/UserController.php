<?php
/**
 * Controller for API based user routes
 */

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Respect\Validation\Validator as v;

/**
 * Class UserController
 * @package Nucleus\Controllers
 */
class UserController extends BaseController
{

    protected $token;

    /**
     * Accept a token object for use within this class
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Check if provided username exists
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function checkUsername($request, $response, $arguments)
    {
        if ( (!isset($arguments['username'])) || (empty($arguments['username'])) ){
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Must provide a username"]));
            return $res;
        }

        $username = $arguments['username'];

        $user = \Nucleus\Models\User::where('username', '=', $username)->first();

        $res = $response->withHeader("Content-Type", "application/json");

        if ( count($user) == 0 ){
            return $res->withStatus(201)->getBody()->write(json_encode(["status" => false]));
        }

        if ( !(bool)$user->active ){
            return $res->withStatus(201)->getBody()->write(json_encode(["status" => -1]));
        }

        return $res->withStatus(201)->getBody()->write(json_encode(["status" => true]));
    }

    /**
     * Login a user with username/password, used to fetch a user's JSON Web Token
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function login($request, $response, $arguments)
    {
        $data = $request->getParsedBody();

        $validation = $this->container->validator->validate($request, [
            'username' => v::notEmpty()->length(4, 20)->stringType()->alnum()->usernameExists(),
            'password' => v::notEmpty()->length(8)->noWhitespace()->stringType()
                ->passwordCheck($data['username']),
        ]);

        if ($validation->failed()) {
            return $response->withStatus(401)->withJson($_SESSION['errors']);
        }

        $user = \Nucleus\Models\User::where('username', '=', $data['username'])->first();

        if (is_null($user)) {
            $this->container['debug.log']->debug($data['username'] . " not found in users");
            return $response->withStatus(401)->withJson(["error" => $data['username'] . " not found in users"]);
        } else {
            $this->container->user_manager->login($user->uuid);
            $user->setContainer($this->container);
            // Find a corresponding token
            $this->container['debug.log']->debug('Looking for token:', $user->toArray());
            if (!$user->getToken()) {
                $this->container['debug.log']->debug(
                    __FILE__ . " on line " . __LINE__ . "\nUnable to retrieve a token.",
                    ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]
                );
                $this->container['error.log']->error(
                    "Unable to retrieve a token.",
                    ['payload' => $data, 'file' => __FILE__, 'line' => __LINE__]
                );
                return $response->withStatus(400);
            }
            $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nToken: " . $user->token->token);
            $out = [
                "token" => $user->token->token,
                "username" => $user->username
            ];
            return $response->withJson($out);
        }
    }

    /**
     * Destroy current session and JSON Web Token
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function logout($request, $response, $arguments)
    {
        $this->container->user_manager->logout();
        return $response->withStatus(200);
    }

    /**
     * Determine a uuid by email then call sendResetCode
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function getResetCodeEmail($request, $response, $arguments)
    {
        if ( (!isset($arguments['email'])) || (empty($arguments['email'])) ){
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Must provide an email address"]));
            return $res;
        }

        $email = $arguments['email'];

        $user = \Nucleus\Models\User::where('email', '=', $email)->first();

        if ( count($user) == 0 ){
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Cannot find an account with the email address " . $email]));
            return $res;
        }

        $arguments['uuid'] = $user->uuid;

        $this->getResetCode($request, $response, $arguments);
    }

    /**
     * Determine a uuid by username then call sendResetCode
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function getResetCodeUsername($request, $response, $arguments)
    {
        if ( (!isset($arguments['username'])) || (empty($arguments['username'])) ){
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Must provide a username"]));
            return $res;
        }

        $username = $arguments['username'];

        $user = \Nucleus\Models\User::where('username', '=', $username)->first();

        if ( count($user) == 0 ){
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Cannot find an account with the username " . $username]));
            return $res;
        }

        $arguments['uuid'] = $user->uuid;

        $this->getResetCode($request, $response, $arguments);
    }

    /**
     * Get a given user's password reset code
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function getResetCode($request, $response, $arguments)
    {
        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nFetching reset code");

        $res = $response->withHeader("Content-Type", "application/json");

        if ( (!isset($arguments['uuid'])) || (empty($arguments['uuid'])) ){
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Must provide a uuid"]));
            return $res;
        }

        $uuid = $arguments['uuid'];

        $user = \Nucleus\Models\User::find($uuid);

        // Return code if current user is an admin
        if ( ($this->container->user_manager->check())
            &&
            ($this->container->user_manager->currentUser()->getAdmin()) ){
            try {
                $code = $user->getResetCode();
            } catch (\Illuminate\Database\QueryException $e) {
                $res = $res->withStatus(400);
                $res->getBody()->write(json_encode(["error" => $e->getMessage()]));
                return $res;
            }

            return $res->withStatus(201)->getBody()->write(json_encode(["code" => $code->getCode()]));
        }

        // Email the code to the user
        if ( is_null($user->container)) {
            $user->setContainer($this->container);
        }
        try {
            $result = $user->sendResetCode();
        } catch (\Exception $e){
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $res;
        }
        return $res->withStatus(201)->getBody()->write(json_encode(["status" => (bool)$result]));
    }

    /**
     * Send a given user's password reset code (admin route)
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function sendResetCode($request, $response, $arguments)
    {
        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nSending reset code");

        $res = $response->withHeader("Content-Type", "application/json");

        if ( (!isset($arguments['uuid'])) || (empty($arguments['uuid'])) ){
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => "Must provide a uuid"]));
            return $res;
        }

        $uuid = $arguments['uuid'];

        $user = \Nucleus\Models\User::find($uuid);

        // Email the code to the user
        if ( is_null($user->container)) {
            $user->setContainer($this->container);
        }
        try {
            $result = $user->sendResetCode();
        } catch (\Exception $e){
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $res;
        }
        return $res->withStatus(201)->getBody()->write(json_encode(["status" => (bool)$result]));
    }

    /**
     * Return an array of all users in the database
     * @param $request
     * @param $response
     * @param $arguments
     * @return collection
     */
    public function getUsers($request, $response, $arguments)
    {
        return $response->getBody()->write(\Nucleus\Models\User::all()->toJson());
    }

    /**
     * Fetch User object for given user as identified by UUID
     * @param $request
     * @param $response
     * @param $arguments
     * @return User
     */
    public function getUser($request, $response, $arguments)
    {
        $uuid = $arguments['uuid'];
        $dev = \Nucleus\Models\User::find($uuid);
        $response->getBody()->write($dev);
        return $response;
    }

    /**
     * Create a new user
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function createUser($request, $response, $arguments)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nCreate user payload:",
            $request->getParsedBody()
        );

        if (!$this->container->user_manager->createUserValidation($request)) {
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["errors" => $_SESSION['errors']]));
            return $res;
        }

        try {
            $user = $this->container->user_manager->createUser($request);
        } catch (\Illuminate\Database\QueryException $e) {
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $res;
        }

        return $response->withStatus(201)->getBody()->write($user->toJson());
    }

    /**
     * Update given user as identified by UUID
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function updateUser($request, $response, $arguments)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nUpdate user payload:",
            $request->getParsedBody()
        );

        $uuid = $arguments['uuid'];
        $data = $request->getParsedBody();

        if (!$this->container->user_manager->updateUserValidationAdmin($request, $uuid)) {
            $res = $response->withHeader("Content-Type", "application/json");
            $res = $res->withStatus(400);
            $res->getBody()->write(json_encode(["errors" => $_SESSION['errors']]));
            return $res;
        }

        $user = $this->container->user_manager->updateUser($data, $uuid);

        return $response->getBody()->write($user->toJson());
    }

    /**
     * Disable given user as identified by UUID
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function deactivateUser($request, $response, $arguments)
    {
        $uuid = $arguments['uuid'];

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nDeactivate user:", [$uuid]);

        $user = $this->container->user_manager->setActive($uuid, false);

        return $response->getBody()->write($user->toJson());
    }

    /**
     * Enable given user as identified by UUID
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function activateUser($request, $response, $arguments)
    {
        $uuid = $arguments['uuid'];

        $user = $this->container->user_manager->setActive($uuid, true);

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nActivate user:", $user->toArray());

        return $response->getBody()->write($user->toJson());
    }

    /**
     * Entirely destroy given user as identified by UUID
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function deleteUser($request, $response, $arguments)
    {
        $uuid = $arguments['uuid'];

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nDelete user:" . $uuid);

        if ($this->container->user_manager->deleteUser($uuid)) {
            return $response->withStatus(200);
        }
        $res = $response->withHeader("Content-Type", "application/json");
        $res = $res->withStatus(400);
        $res->getBody()->write(json_encode(["errors" => "Failed to delete user."]));
        return $res;
    }
}

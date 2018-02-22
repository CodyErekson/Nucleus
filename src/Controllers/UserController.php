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
     * Login a user with username/password, used to fetch a user's JSON Web Token
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function login($request, $response, $args)
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
     * @param $args
     * @return mixed
     */
    public function logout($request, $response, $args)
    {
        $this->container->user_manager->logout();
        return $response->withStatus(200);
    }

    /**
     * Return an array of all users in the database
     * @param $request
     * @param $response
     * @param $args
     * @return collection
     */
    public function getUsers($request, $response, $args)
    {
        return $response->getBody()->write(\Nucleus\Models\User::all()->toJson());
    }

    /**
     * Fetch User object for given user as identified by UUID
     * @param $request
     * @param $response
     * @param $args
     * @return User
     */
    public function getUser($request, $response, $args)
    {
        $uuid = $args['uuid'];
        $dev = \Nucleus\Models\User::find($uuid);
        $response->getBody()->write($dev);
        return $response;
    }

    /**
     * Create a new user
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function createUser($request, $response, $args)
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
     * @param $args
     * @return mixed
     */
    public function updateUser($request, $response, $args)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nUpdate user payload:",
            $request->getParsedBody()
        );

        $uuid = $args['uuid'];
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
     * @param $args
     * @return mixed
     */
    public function deactivateUser($request, $response, $args)
    {
        $uuid = $args['uuid'];

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nDeactivate user:", [$uuid]);

        $user = $this->container->user_manager->setActive($uuid, false);

        return $response->getBody()->write($user->toJson());
    }

    /**
     * Enable given user as identified by UUID
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function activateUser($request, $response, $args)
    {
        $uuid = $args['uuid'];

        $user = $this->container->user_manager->setActive($uuid, true);

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nActivate user:", $user->toArray());

        return $response->getBody()->write($user->toJson());
    }

    /**
     * Entirely destroy given user as identified by UUID
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function deleteUser($request, $response, $args)
    {
        $uuid = $args['uuid'];

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

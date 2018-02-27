<?php
/**
 * Controller for interface based user routes
 */

namespace Nucleus\Controllers;

use Nucleus\Controllers\BaseController;
use Nucleus\Models\User;
use Respect\Validation\Validator as v;

/**
 * Class AuthController
 * @package Nucleus\Controllers
 */
class AuthController extends BaseController
{

    /**
     * Draw the signup page
     * @param $request
     * @param $response
     * @return mixed
     */
    public function getSignUp($request, $response)
    {
        return $this->container->view->render($response, 'signup.twig');
    }

    /**
     * Validate submitted user data then create a new user
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postSignUp($request, $response)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nCreate user payload:",
            $request->getParsedBody()
        );

        if (!$this->container->user_manager->createUserValidation($request)) {
            return $response->withRedirect($this->container->router->pathFor('auth.signup'));
        }

        try {
            $user = $this->container->user_manager->createUser($request);
            $this->container->user_manager->login($user->uuid);
        } catch (\Illuminate\Database\QueryException $e) {
            return $response->withRedirect($this->container->router->pathFor('auth.signup'));
        }

        $this->container->flash->addMessage('success', 'Your account has been successfully created.');

        return $response->withRedirect($this->container->router->pathFor('home'));
    }

    /**
     * Draw the login page
     * @param $request
     * @param $response
     * @return mixed
     */
    public function getLogin($request, $response)
    {
        return $this->container->view->render($response, 'login.twig');
    }

    /**
     * Validate login credentials then create a user session and JWT token
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postLogin($request, $response)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nLogin payload:",
            $request->getParsedBody()
        );

        if (!$this->container->user_manager->loginValidation($request)) {
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }

        try {
            $user = \Nucleus\Models\User::where('username', $request->getParam('username'))
                ->where('active', true)->first();
            $this->container->user_manager->login($user->uuid);
        } catch (\Exception $e) {
            $this->container['error.log']->error(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }

        return $response->withRedirect($this->container->router->pathFor('home'));
    }

    /**
     * Log a user out
     * @param $request
     * @param $response
     * @return mixed
     */
    public function getLogout($request, $response)
    {
        $this->container->user_manager->logout();

        return $response->withRedirect($this->container->router->pathFor('home'));
    }

    /**
     * Validate reset code and email then create a user session and JWT token
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postLoginReset($request, $response)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nReset password payload:",
            $request->getParsedBody()
        );

        if (!$this->container->user_manager->resetCodeValidation($request)) {
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }

        try {
            $user = \Nucleus\Models\User::where('username', $request->getParam('cpusername'))
                ->where('active', true)->first();
            $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nUser " . $user->uuid);
            $this->container->user_manager->login($user->uuid);
            $user->destroyResetCode();
        } catch (\Exception $e) {
            $this->container['error.log']->error(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }

        try {
            $user = $this->container->user_manager->changePassword($request->getParam('cppassword'), $user->uuid);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }

        $this->container->flash->addMessage('success', 'Your password has been changed.');

        return $response->withRedirect($this->container->router->pathFor('home'));
    }

    /**
     * Draw the edit user page
     * @param $request
     * @param $response
     * @return mixed
     */
    public function getUpdateUser($request, $response)
    {
        return $this->container->view->render($response, 'update_profile.twig');
    }

    /**
     * Validate submitted parameters and update given user
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postUpdateUser($request, $response)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nUpdate user payload:",
            $request->getParsedBody()
        );

        $user = $this->container->user_manager->currentUser();
        if (!$this->container->user_manager->updateUserValidation($request, $user->uuid)) {
            return $response->withRedirect($this->container->router->pathFor('auth.user.update'));
        }

        try {
            $user = $this->container->user_manager->updateUser($request->getParsedBody(), $user->uuid);
            $this->container->user_manager->login($user->uuid);
        } catch (\Illuminate\Database\QueryException $e) {
            return $response->withRedirect($this->container->router->pathFor('auth.user.update'));
        }

        $this->container->flash->addMessage('success', 'Your account has been successfully modified.');

        return $response->withRedirect($this->container->router->pathFor('auth.user.update'));
    }

    /**
     * Draw the change password page
     * @param $request
     * @param $response
     * @return mixed
     */
    public function getPasswordChange($request, $response)
    {
        return $this->container->view->render($response, 'change_password.twig');
    }

    /**
     * Validate the provided password and set as given user's password
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postPasswordChange($request, $response)
    {
        if (!$this->container->user_manager->check()) {
            $this->container->flash->addMessage('error', 'Must be logged in to change password.');
            return $response->withRedirect($this->container->router->pathFor('auth.user.password'));
        }

        if (!$this->container->user_manager->changePasswordValidation($request)) {
            $this->container->flash->addMessage('error', 'Unable to change password.');
            return $response->withRedirect($this->container->router->pathFor('auth.user.password'));
        }

        try {
            $user = $this->container->user_manager->currentUser();
            $user = $this->container->user_manager->changePassword($request->getParam('password'), $user->uuid);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
            return $response->withRedirect($this->container->router->pathFor('auth.login'));
        }

        $this->container->flash->addMessage('success', 'Your password has been changed.');

        return $response->withRedirect($this->container->router->pathFor('home'));
    }
}

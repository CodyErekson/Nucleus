<?php
/**
 * Controller for home route -- mostly used for testing at this point
 */

namespace Nucleus\Controllers;

use Respect\Validation\Validator as v;

/**
 * Class DashboardController
 * @package Nucleus\Controllers
 */
class DashboardController extends BaseController
{
    /**
     * Display the dashboard
     * @param $request
     * @param $response
     * @param $arguments
     * @return request
     */
    public function getDashboard($request, $response, $arguments)
    {
        return $this->container->view->render($response, 'dashboard.twig');
    }

    /**
     * Receive and process POST request from dashboard
     * @param $request
     * @param $response
     * @param $arguments
     */
    public function postDashboard($request, $response, $arguments)
    {
    }

    /**
     * Display settings page
     * @param $request
     * @param $response
     * @param $arguments
     * @return request
     */
    public function getSettings($request, $response, $arguments)
    {
        $settings = \Nucleus\Models\Setting::all();

        $arguments['settings'] = [];

        foreach ($settings as $setting) {
            $arguments['settings'][] = [
                "id" => $setting->id,
                "setting" => $setting->setting,
                "value" => $setting->value,
                "env" => $setting->env
            ];
        }

        return $this->container->view->render($response, 'dashboard.settings.twig', $arguments);
    }

    /**
     * Receive and process POST request from settings page
     * @param $request
     * @param $response
     * @param $arguments
     * @return mixed
     */
    public function postSettings($request, $response, $arguments)
    {

        $datas = $request->getParsedBody();

        foreach ($datas as $id => $data) {
            if (is_array($data)) {
                $setting = \Nucleus\Models\Setting::find($id);
                $data = $request->getParam((int)$id);
                $skip = false;
                if ($setting->allow_null) {
                    if ((strlen($data['value']) == 0) || (empty($data['value'])) || (is_null($data['value']))) {
                        $skip = true;
                    }
                }
                if (!isset($data['env'])) {
                    $data['env'] = false;
                }
                $data['env'] = (bool)$data['env'];
                $validation = $this->container->validator->validateArray($data, [
                    'setting' => v::notEmpty()->noWhitespace()->length(4, 255)->stringType()->uppercase()->alnum('_'),
                    'value' => $skip ? v::alwaysValid() : v::notOptional()->noWhitespace()->length(1, 255),
                    'env' => v::boolType()
                ], $id);
            } else {
                continue;
            }
            if ($validation->failed()) {
                return $response->withRedirect($this->container->router->pathFor('dashboard.settings'));
            }

            // Here is where we actually save the new settings
            if ($setting->setting != $data['setting']) {
                $setting->setName($data['setting']);
            }
            if ($setting->value != $data['value']) {
                $setting->setValue($data['value']);
            }
            if ($setting->env != $data['env']) {
                $setting->setEnv($data['env']);
            }
            $setting->save();
        }

        return $response->withRedirect($this->container->router->pathFor('dashboard.settings'));
    }

    /**
     * Display user management page
     * @param $request
     * @param $response
     * @param $arguments
     * @return request
     */
    public function getUsers($request, $response, $arguments)
    {
        $users = \Nucleus\Models\User::all();

        $arguments['users'] = [];

        foreach ($users as $user) {
            $arguments['users'][] = [
                "uuid" => $user->uuid,
                "username" => $user->username,
                "email" => $user->email,
                "admin" => $user->getAdmin(),
                "active" => $user->active
            ];
        }

        return $this->container->view->render($response, 'dashboard.users.twig', $arguments);
    }

    /**
     * Display user edit page
     * @param $request
     * @param $response
     * @param $arguments
     * @return request
     */
    public function getUser($request, $response, $arguments)
    {
        $user = \Nucleus\Models\User::find($arguments['uuid']);

        $arguments['user'] = [
            "uuid" => $user->uuid,
            "username" => $user->username,
            "email" => $user->email,
            "admin" => $user->getAdmin(),
            "active" => $user->active
        ];

        return $this->container->view->render($response, 'dashboard.user.twig', $arguments);
    }

    /**
     * Validate submitted parameters and update given user
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postUser($request, $response, $arguments)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nUpdate user payload:",
            $request->getParsedBody()
        );

        $user = \Nucleus\Models\User::find($arguments['uuid']);
        if (!$this->container->user_manager->updateUserValidation($request, $user->uuid)) {
            $this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ .
                "\nFailed validation: " . json_encode($_SESSION['errors']));
            return $response->withRedirect($this->container->router->pathFor('dashboard.user', ['uuid' => $user->uuid]));
        }

        $data = $request->getParsedBody();

        $active = ($data['active'] == "on" ) ? true : false;
        $admin = ($data['admin'] == "on" ) ? true : false;
        unset($data['active']);
        unset($data['admin']);

        try {
            $user = $this->container->user_manager->updateUser($data, $user->uuid);
            $user->setAdmin($admin);
            $user->setActive($active);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
            return $response->withRedirect($this->container->router->pathFor('dashboard.user', ['uuid' => $user->uuid]));
        }

        $this->container->flash->addMessage('success', $user->username . ' has been successfully modified.');

        return $response->withRedirect($this->container->router->pathFor('dashboard.users'));
    }

    /**
     * Validate submitted parameters and update given user's password
     * @param $request
     * @param $response
     * @return mixed
     */
    public function postUserPassword($request, $response, $arguments)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nUpdate user payload:",
            $request->getParsedBody()
        );

        $user = \Nucleus\Models\User::find($arguments['uuid']);
        if (!$this->container->user_manager->changePasswordValidationAdmin($request, $user->uuid)) {
            $this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ .
                "\nFailed validation: " . json_encode($_SESSION['errors']));
            return $response->withRedirect($this->container->router->pathFor('dashboard.user', ['uuid' => $user->uuid]));
        }

        try {
            $user = $this->container->user_manager->changePassword($request->getParam('password'), $user->uuid);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->container['error.log']->debug(__FILE__ . " on line " . __LINE__ . "\nerror: " . $e->getMessage());
            return $response->withRedirect($this->container->router->pathFor('dashboard.user', ['uuid' => $user->uuid]));
        }

        $this->container->flash->addMessage('success', $user->username . ' has been successfully modified.');

        return $response->withRedirect($this->container->router->pathFor('dashboard.users'));
    }

    /**
     * Validate submitted user data then create a new user
     * @param $request
     * @param $response
     * @return mixed
     */
    public function createUser($request, $response)
    {
        $this->container['debug.log']->debug(
            __FILE__ . " on line " . __LINE__ . "\nCreate user payload:",
            $request->getParsedBody()
        );

        if (!$this->container->user_manager->createUserValidation($request)) {
            return $response->withRedirect($this->container->router->pathFor('dashboard.users'));
        }

        try {
            $user = $this->container->user_manager->createUser($request);
        } catch (\Illuminate\Database\QueryException $e) {
            return $response->withRedirect($this->container->router->pathFor('dashboard.users'));
        }

        $this->container->flash->addMessage('success', 'New user account has been successfully created.');

        return $response->withRedirect($this->container->router->pathFor('dashboard'));
    }

}

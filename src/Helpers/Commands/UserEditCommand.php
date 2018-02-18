<?php

namespace Nucleus\Helpers\Commands;

class UserEditCommand extends BaseCommand
{
    /**
     * Create a new user
     * @param $arguments
     */
    public function command($arguments)
    {
        $this->cli->arguments->add([
            'user' => [
                'prefix' => 'U',
                'longPrefix' => 'user',
                'description' => 'Current username',
                'required' => true
            ],
            'username' => [
                'prefix' => 'u',
                'longPrefix' => 'username',
                'description' => 'Change username',
                'defaultValue' => false
            ],
            'email' => [
                'prefix' => 'e',
                'longPrefix' => 'email',
                'description' => 'Change email address',
                'defaultValue' => false
            ],
            'password' => [
                'prefix' => 'p',
                'longPrefix' => 'password',
                'description' => 'Change password',
                'noValue' => true
            ],
            'active' => [
                'prefix' => 'a',
                'longPrefix' => 'active',
                'description' => 'Toggle active state [true/false]',
                'castTo' => 'string'
            ],
            'admin' => [
                'prefix' => 'A',
                'longPrefix' => 'admin',
                'description' => 'Toggle admin role [true/false]',
                'castTo' => 'string'
            ],
            'help' => [
                'longPrefix'  => 'help',
                'description' => 'Prints a usage statement',
                'noValue'     => true
            ]
        ]);

        try {
            $this->cli->arguments->parse();
        } catch (\Exception $e) {
            $this->cli->out("\n" . $e->getMessage() . "\n");
            $this->cli->usage();
            exit();
        }

        if ($this->cli->arguments->defined('help')) {
            $this->cli->usage();
            exit();
        }

        // Load the user if we can
        $user = \Nucleus\Models\User::where("username", "=", $this->cli->arguments->get('user'))->first();

        if (is_null($user)) {
            $this->cli->red()->out("User " . $this->cli->arguments->get('user') . " is invalid.");
            exit();
        }

        $user_array = [
            'username' => $user->username,
            'email' => $user->email
        ];

        $update = false;
        // Update username
        $username = $this->cli->arguments->get('username');
        if ($username) {
            $user_array['username'] = ucfirst($username);
            $update = true;
        }

        // Update email address
        $email = $this->cli->arguments->get('email');
        if ($email) {
            $user_array['email'] = $email;
            $update = true;
        }

        // Validate and change username and email
        if ($update) {
            if (!$this->container->user_manager->updateUserValidation($user_array, $user->uuid)) {
                $this->cli->red()->out("Could not update user due to the following errors:\n");
                foreach ($_SESSION['errors'] as $errors) {
                    foreach ($errors as $error) {
                        $this->cli->tab()->out($error);
                    }
                }
                exit();
            } else {
                try {
                    $user = $this->container->user_manager->updateUser($user_array, $user->uuid);
                } catch (\Exception $e) {
                    $this->cli->red()->out("Could not update user.");
                    exit();
                }
                if ($username && $email) {
                    $this->cli->green()->out("Successfully updated username and email address.");
                } elseif ($username && !$email) {
                    $this->cli->green()->out("Successfully updated username.");
                } elseif (!$username && $email) {
                    $this->cli->green()->out("Successfully updated email address.");
                }
            }
        }

        // Change the password
        if ($this->cli->arguments->defined('password')) {
            $password = $confirm = $this->password($user->username);
            $this->cli->out("\n");
            if (!$this->container->user_manager->changePasswordValidationAdmin($password)) {
                $this->cli->red()->out("Could not change password.");
                foreach ($_SESSION['errors'] as $errors) {
                    foreach ($errors as $error) {
                        $this->cli->tab()->out($error);
                    }
                }
                exit();
            } else {
                try {
                    $user = $this->container->user_manager->changePassword($password, $user->uuid);
                } catch (\Exception $e) {
                    $this->cli->red()->out("Could not change password.");
                    exit();
                }
            }
            $this->cli->green()->out("Successfully changed password.");
        }

        // Set active state
        if ($this->cli->arguments->defined('active')) {
            $state = $this->cli->arguments->get('active');
            if ($state == "true") {
                $state = 1;
            } elseif ($state == "false") {
                $state = 0;
            } else {
                $this->cli->red()->out("Only [true/false] allowed.");
                exit();
            }
            if ($user->setActive($state)) {
                if ($state) {
                    $this->cli->green()->out("Successfully activated " . $user->username . ".");
                } else {
                    $this->cli->green()->out("Successfully deactivated " . $user->username . ".");
                }
            } else {
                if ($state) {
                    $this->cli->red()->out("Could not activate " . $user->username . ".");
                } else {
                    $this->cli->red()->out("Could not deactivate " . $user->username . ".");
                }
                exit();
            }
        }

        // Set admin state
        if ($this->cli->arguments->defined('admin')) {
            $state = $this->cli->arguments->get('admin');
            if ($state == "true") {
                $state = 1;
            } elseif ($state == "false") {
                $state = 0;
            } else {
                $this->cli->red()->out("Only [true/false] allowed.");
                exit();
            }
            if ($user->setAdmin($state)) {
                if ($state) {
                    $this->cli->green()->out("Successfully assigned " . $user->username . " as an admin.");
                } else {
                    $this->cli->green()->out("Successfully unassigned " . $user->username . " as an admin.");
                }
            } else {
                if ($state) {
                    $this->cli->red()->out("Could not assign " . $user->username . " as an admin.");
                } else {
                    $this->cli->red()->out("Could not unassign " . $user->username . " as an admin.");
                }
                exit();
            }
        }
    }

    private function password($username)
    {
        $input = $this->cli->password("Create a password for " . $username . ":");
        $password = $input->prompt();
        $input = $this->cli->password("\nConfirm password:");
        $confirm = $input->prompt();

        if ($password != $confirm) {
            $this->cli->red()->out("\nPassword and confirmation do not match!\n");
            $password = $this->password($username);
        }
        return $password;
    }
}

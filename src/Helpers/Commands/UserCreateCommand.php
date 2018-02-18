<?php

namespace Nucleus\Helpers\Commands;

class UserCreateCommand extends BaseCommand
{
    /**
     * Create a new user
     * @param $arguments
     */
    public function command($arguments)
    {
        $this->cli->arguments->add([
            'username' => [
                'prefix' => 'u',
                'longPrefix' => 'username',
                'description' => 'New username',
                'required' => true
            ],
            'email' => [
                'prefix' => 'e',
                'longPrefix' => 'email',
                'description' => 'Email address for user',
                'required' => true
            ],
            'help' => [
                'longPrefix'  => 'help',
                'description' => 'Prints a usage statement',
                'noValue'     => true,
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

        $user['username'] = ucfirst($this->cli->arguments->get('username'));
        $user['email'] = $this->cli->arguments->get('email');

        // Get a password
        $user['password'] = $user['confirm'] = $this->password($user['username']);
        $this->cli->out("\n");

        // Determine role
        $input = $this->cli->input("Set " . $user['username'] . " as admin?");
        $input->accept(['y', 'n'], true);
        $response = $input->prompt();

        if ($response == "y") {
            $user['admin'] = true;
            $rolesText = "member, admin";
        } else {
            $user['admin'] = false;
            $rolesText = "member";
        }

        // Confirm
        $this->cli->out("\nUsername: " . $user['username']);
        $this->cli->out("Email: " . $user['email']);
        $this->cli->out("Role(s): " . $rolesText);
        $input = $this->cli->input("Proceed with user creation?");
        $input->accept(['y', 'n'], true);
        $response = $input->prompt();

        if ($response == 'n') {
            $this->cli->red()->out("User " . $user['username'] . " was NOT created.");
            exit();
        }

        // Now let's validate and create our user
        if (!$this->container->user_manager->createUserValidation($user)) {
            $this->cli->red()->out("Could not create user due to the following errors:\n");
            foreach ($_SESSION['errors'] as $errors) {
                foreach ($errors as $error) {
                    $this->cli->tab()->out($error);
                }
            }
        } else {
            $new_user = $this->container->user_manager->createUser($user);
            if ($user['admin']) {
                $new_user->addRole(3);
            }
            $this->cli->green()->out("Successfully created the user " . $new_user->username . "\n");
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

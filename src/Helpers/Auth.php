<?php

namespace Nucleus\Helpers;

use Nucleus\Models\User;

class Auth
{

	public function user()
	{
		if ( isset($_SESSION['uuid']) ) {
			return User::find($_SESSION['uuid']);
		}

		return false;
	}

	public function check()
	{
		return isset($_SESSION['uuid']);
	}

	public function validate($username, $password)
	{
		$user = User::where('username', $username)->first();

		if ( !$user ){
			return false;
		}

		if ( password_verify($password, $user->password) ){
			$_SESSION['uuid'] = $user->uuid;
			return true;
		}

		return false;
	}

	public function getRole()
	{
		$user = User::find($_SESSION['uuid']);
		return $user->role;
	}

	public function logout()
	{
		if ( isset($_SESSION['uuid']) ) {
			unset($_SESSION['uuid']);
		}
	}

}
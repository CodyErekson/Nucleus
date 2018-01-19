<?php

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;

class Role extends Model {
	protected $table = 'roles';

	protected $fillable = [
		'role'
	];

	public function users()
	{
		return $this->belongsToMany('\Nucleus\Models\User', 'role_user');
	}

	public function getUsers()
	{
		$users = [];
		foreach($this->roles as $user){
			$users[$user->uuid] = ['username' => $user->username];
		}
		return $users;
	}
}
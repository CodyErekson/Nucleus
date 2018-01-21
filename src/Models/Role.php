<?php
/**
 * Role object
 */

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Role
 * @package Nucleus\Models
 */
class Role extends Model {
	protected $table = 'roles';

	protected $fillable = [
		'role'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users()
	{
		return $this->belongsToMany('\Nucleus\Models\User', 'role_user');
	}

	/**
	 * Fetch the users assigned to this role
	 * @return array
	 */
	public function getUsers()
	{
		$users = [];
		foreach($this->roles as $user){
			$users[$user->uuid] = ['username' => $user->username];
		}
		return $users;
	}
}
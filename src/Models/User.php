<?php

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;

class User extends Model {
	protected $table = 'users';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'username',
		'email',
		'password',
		'role',
		'active'
	];

	public function setPassword($password)
	{
		$this->update([
			'password' => password_hash($password, PASSWORD_BCRYPT)
		]);
	}

	public function setActive($state = false)
	{
		$this->update([
			'active' => $state
		]);
	}
}
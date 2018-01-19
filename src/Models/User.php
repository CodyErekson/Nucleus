<?php

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;
use Nucleus\Models\Role;

class User extends Model {
	protected $table = 'users';
	protected $primaryKey = 'uuid';
	protected $container = null;
	public $incrementing = false;

	protected $fillable = [
		'uuid',
		'username',
		'email',
		'password',
		'active'
	];

	// Relationships
	public function token()
	{
		return $this->hasOne('\Nucleus\Models\Token', 'uuid');
	}

	public function roles()
	{
		return $this->belongsToMany('\Nucleus\Models\Role', 'role_user');
	}

	// Set methods
	public function setContainer(\Slim\Container $container)
	{
		$this->container = $container;
	}

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

	// Use methods
	public function getRoles()
	{
		$roles = [];
		foreach($this->roles as $role){
			$roles[$role->id] = ['role' => $role->role];
		}
		return $roles;
	}

	public function getToken()
	{
		if ( ( is_null($this->token) ) || ( !$this->token->isValid() ) ) {
			//we need to create a new token
			$jwt = $this->createToken();
			if ( !$jwt ) {
				return false;
			} else {
				if ( is_null($this->token) ) {
					$this->token = Token::create([
						'uuid' => $this->uuid,
						'token' => $jwt,
						'expiration' => date('Y-m-d H:i:s', time() + (3600 * 24 * 15))
					]);
				} else {
					$this->token->updateToken($jwt);
				}
			}
		}
		return $this->token;
	}

	public function createToken()
	{
		$key = base64_encode(getenv('JWT_SECRET'));
		$payload = array(
			"jti"	=> base64_encode(random_bytes(32)),
			"iat"   => time(),
			"iss"   => getenv('HOST'),
			"nbf"	=> time() + 10,
			"exp"   => time() + (3600 * 24 * 15),
			"data" => [
				"user" => [
					"username" => $this->username,
					"uuid"    => $this->uuid
				]
			]
		);
		try {
			return $this->container->jwt::encode($payload, $key, 'HS256');
		} catch ( Exception $e ){
			return false;
		}
	}

}
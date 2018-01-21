<?php
/**
 * User object
 */

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;
use Nucleus\Models\Role;

/**
 * Class User
 * @package Nucleus\Models
 */
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

	protected $hidden = [
		'password'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function token()
	{
		return $this->hasOne('\Nucleus\Models\Token', 'uuid');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany('\Nucleus\Models\Role', 'role_user');
	}

	/**
	 * Pass the DIC into user object
	 * @param \Slim\Container $container
	 */
	public function setContainer(\Slim\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Define the user's password
	 * @param $password
	 */
	public function setPassword($password)
	{
		$this->update([
			'password' => password_hash($password, PASSWORD_BCRYPT)
		]);
	}

	/**
	 * Enable or disable user
	 * @param bool $state
	 */
	public function setActive($state = false)
	{
		$this->update([
			'active' => $state
		]);
	}

	/**
	 * Fetch this user's roles
	 * @return array
	 */
	public function getRoles()
	{
		$roles = [];
		foreach($this->roles as $role){
			$roles[$role->id] = ['role' => $role->role];
		}
		return $roles;
	}

	/**
	 * Fetch the current token if available and valid, otherwise create and return new token
	 * @return bool|Token
	 */
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

	/**
	 * Create a new JSON Web Token for user
	 * @return bool|Token
	 */
	public function createToken()
	{
		$key = base64_encode(getenv('JWT_SECRET'));
		$payload = array(
			"jti"	=> base64_encode(random_bytes(32)),
			"iat"   => time(),
			"iss"   => getenv('BASE_URL'),
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
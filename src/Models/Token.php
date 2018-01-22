<?php
/**
 * JSON Web Token object
 */

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;
use SebastianBergmann\Comparator\DateTimeComparator;

/**
 * Class Token
 * @package Nucleus\Models
 */
class Token extends Model {
	protected $table = 'tokens';

	protected $fillable = [
		'uuid',
		'token',
		'expiration'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function user()
	{
		return $this->hasOne('\Nucleus\Models\User', 'uuid');
	}

	/**
	 * Return the user associated with the given token
	 * @return User
	 */
	public function getUser(){
		return $this->user;
	}

	/**
	 * Check if this token is expired or not
	 * @return bool
	 */
	public function isValid()
	{
		$expiration = new \DateTime($this->expiration);
		$now = new \DateTime();
		return ( $expiration > $now );
	}

	/**
	 * Update the actual token and expiration date
	 * @param $jwt
	 */
	public function updateToken($jwt)
	{
		$this->update([
			'token' => $jwt,
			'expiration' => date('Y-m-d H:i:s', time() + (3600 * 24 * 15))
		]);
	}
}
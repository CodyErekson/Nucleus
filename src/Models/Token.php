<?php

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;
use SebastianBergmann\Comparator\DateTimeComparator;

class Token extends Model {
	protected $table = 'tokens';

	protected $fillable = [
		'uuid',
		'token',
		'expiration'
	];

	public function user()
	{
		return $this->hasOne('\Nucleus\Models\User', 'uuid');
	}

	/**
	 * Return the user associated with the given token
	 */
	public function getUser(){
		return $this->user;
	}

	public function isValid()
	{
		$expiration = new \DateTime($this->expiration);
		$now = new \DateTime();
		return ( $expiration > $now );
	}

	public function updateToken($jwt)
	{
		$this->update([
			'token' => $jwt,
			'expiration' => date('Y-m-d H:i:s', time() + (3600 * 24 * 15))
		]);
	}
}
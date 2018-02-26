<?php
/**
 * JSON Web Token object
 */

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;
use SebastianBergmann\Comparator\DateTimeComparator;

/**
 * Class ResetCode
 * @package Nucleus\Models
 */
class ResetCode extends Model
{
    protected $table = 'reset_codes';

    protected $fillable = [
        'uuid',
        'code',
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
     * Return the user associated with the given code
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getCode()
    {
        return bin2hex(base64_decode($this->code));
    }

    /**
     * Check if this code is expired or not
     * @return bool
     */
    public function isValid()
    {
        $expiration = new \DateTime($this->expiration);
        $now = new \DateTime();
        return ( $expiration > $now );
    }

    /**
     * Update the actual code and expiration date
     * @param $code
     */
    public function updateCode($code)
    {
        $this->update([
            'code' => $code,
            'expiration' => date('Y-m-d H:i:s', time() + (3600 * 24 * 2))
        ]);
    }
}

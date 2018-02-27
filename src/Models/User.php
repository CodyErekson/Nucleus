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
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'uuid';
    protected $container = null;
    public $incrementing = false;
    protected $admin = false;

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function resetCode()
    {
        return $this->hasOne('\Nucleus\Models\ResetCode', 'uuid');
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
     * Toggle admin status
     * @param bool
     * @return bool
     */
    public function setAdmin($state)
    {
        $role = \Nucleus\Models\Role::where("role", "=", "admin")->first();
        if ($state) { //add
            return $this->addRole($role->id);
        } else { //remove
            return $this->removeRole($role->id);
        }
    }

    /**
     * Return admin status
     * @return bool
     */
    public function getAdmin()
    {
        return $this->roles->contains('role', 'admin');
    }

    /**
     * Define the user's password
     * NOTE: this does not seem to work for some reason
     * @param $password
     * @return bool
     */
    public function setPassword($password)
    {
        $this->password == password_hash($password, PASSWORD_BCRYPT);
        return $this->save();
    }

    /**
     * Enable or disable user
     * @param bool $state
     * @return bool
     */
    public function setActive($state = false)
    {
        $this->active = $state;
        return $this->save();
    }

    /**
     * Fetch this user's roles
     * @return array
     */
    public function getRoles()
    {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[$role->role] = true;
        }
        return $roles;
    }

    /**
     * Find out if given role is already assign to the user
     * @param $role
     * @return mixed
     */
    public function isRoleAssigned($role)
    {
        return $this->roles->contains('role', $role->role);
    }

    /**
     * Assign a role to the user
     * @param $id
     * @return bool
     */
    public function addRole($id)
    {
        $role = \Nucleus\Models\Role::find($id);
        if ($role->role == "guest") {
            // Do not assign guest role
            return false;
        }
        if (is_null($role)) {
            return false;
        }
        if (!$this->isRoleAssigned($role)) {
            $this->roles()->attach($role);
            $this->load('roles');
        }
        return true;
    }

    /**
     * Remove a role from the user
     * @param $id
     * @return bool
     */
    public function removeRole($id)
    {
        $role = \Nucleus\Models\Role::find($id);
        if ($role->role == "guest") {
            // Do not do anything with guest role
            return true;
        }
        if (is_null($role)) {
            return false;
        }
        $this->roles()->detach($role);
        $this->load('roles');
        return true;
    }

    /**
     * Fetch the current token if available and valid, otherwise create and return new token
     * @return bool|Token
     */
    public function getToken()
    {
        if (( is_null($this->token) ) || ( !$this->token->isValid() )) {
            //we need to create a new token
            $jwt = $this->createToken();
            if (!$jwt) {
                return false;
            } else {
                if (is_null($this->token)) {
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
            "jti"   => base64_encode(random_bytes(32)),
            "iat"   => time(),
            "iss"   => getenv('BASE_URL'),
            "nbf"   => time() + 10,
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
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the user's reset code, or have a new one made
     * @return bool|mixed
     */
    public function getResetCode()
    {
        if (( is_null($this->resetCode) ) || ( !$this->resetCode->isValid() )) {
            //we need to create a new reset code
            $code = $this->createResetCode();
            if (!$code) {
                return false;
            } else {
                if (is_null($this->resetCode)) {
                    $this->resetCode = ResetCode::create([
                        'uuid' => $this->uuid,
                        'code' => $code,
                        'expiration' => date('Y-m-d H:i:s', time() + (3600 * 24 * 2))
                    ]);
                } else {
                    $this->resetCode->updateCode($code);
                }
            }
        }
        return $this->resetCode;
    }

    /**
     * Generate and return a new reset code
     * @return bool|string
     */
    public function createResetCode()
    {
        try {
            return base64_encode(random_bytes(16));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Reset codes are one-time use
     * @return mixed
     */
    public function destroyResetCode()
    {
        return $this->resetCode()->delete();
    }

    /**
     * See if user's reset code is expired
     * @return boolean
     */
    public function checkResetCode()
    {
        return $this->resetCode()->isValid();
    }

    /**
     * Email the user their reset code
     * @return boolean|mixed
     */
    public function sendResetCode()
    {
        if (is_null($this->container)) {
            //we don't have a container, therefore no mail handler
            return false;
        }
        $code = $this->getResetCode()->getCode();

        $mailer = $this->container->mailer;

        $message = $this->container->email;
        $message->setSubject('Temporary access key for ' . getenv('NAME'))
            ->setFrom([getenv('APP_EMAIL_ADDRESS') => getenv('NAME')])
            ->setTo([$this->email])
            ->setBody("Your temporary access key is " . $code); //TODO -- figure out email templates

        return $mailer->send($message);
    }

    /**
     * Destroy the token and session for logged in user
     */
    public function logout()
    {
        if (!is_null($this->token)) {
            $this->token->delete();
        }
        if (isset($_SESSION['uuid'])) {
            unset($_SESSION['uuid']);
            session_destroy();
            setcookie('token', '', time() - 3600, '/', getenv('DOMAIN'));
        }
    }
}

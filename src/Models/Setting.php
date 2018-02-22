<?php
/**
 * Global settings object
 */

namespace Nucleus\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Setting
 * @package Nucleus\Models
 */
class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = [
        'setting',
        'value',
        'allow_null',
        'env'
    ];

    /**
     * Define setting name
     * @param $name
     */
    public function setName($name)
    {
        $this->setting = $name;
        $this->save();
    }

    /**
     * Define setting value
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->save();
    }

    /**
     * Define allow_null value
     * @param boolean $val
     */
    public function setAllowNull(bool $val)
    {
        $this->allow_null = $val;
        $this->save();
    }

    /**
     * Define env value
     * @param boolean $val
     */
    public function setEnv(boolean $val)
    {
        $this->env = $val;
        $this->save();
    }
}

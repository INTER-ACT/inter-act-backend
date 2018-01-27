<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.01.18
 * Time: 19:48
 */

namespace App\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

abstract class AuthRestModel extends Authenticatable implements IRestResource
{
    function getId() : int
    {
        return $this->id;
    }

    public function getType() : string
    {
        return get_class($this);
    }

    public abstract function getApiFriendlyType() : string;
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.01.18
 * Time: 19:43
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

abstract class RestModel extends Model implements IRestResource
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
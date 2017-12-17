<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.12.17
 * Time: 09:36
 */

namespace App\Traits;


use Illuminate\Database\Query\Builder;

trait TPost
{
    //public abstract function user();

    public function scopeOfUser(Builder $query, int $user_id)
    {
        return $query->where('user_id', '=', $user_id);
    }
}
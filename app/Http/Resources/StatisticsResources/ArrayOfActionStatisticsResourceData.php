<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 03.01.18
 * Time: 08:36
 */

namespace App\Http\Resources\StatisticsResources;


class ArrayOfActionStatisticsResourceData extends \ArrayObject
{
    public function offsetSet($key, $val) {
        if ($val instanceof GeneralActivityStatisticsResourceData) {
            return parent::offsetSet($key, $val);
        }
        throw new \InvalidArgumentException('Value must be an GeneralActivityStatisticsResourceData');
    }

    public function getArrayForm()
    {

    }
}
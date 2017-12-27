<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 26.12.17
 * Time: 15:27
 */

namespace App\Http\Resources;


trait ResourceFieldFilterTrait
{
    protected $excludedFields = [];

    /**
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        foreach ($fields as $field)
        {
            if(!in_array($field, $this->excludedFields))
                $this->excludedFields[] = $field;
        }
        return $this;
    }

    /**
     * @param $array
     * @return array
     */
    public function hideFields($array)
    {
        return collect($array)->forget($this->excludedFields)->toArray();
    }
}
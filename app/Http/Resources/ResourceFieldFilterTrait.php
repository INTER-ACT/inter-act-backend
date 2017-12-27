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
    protected $fieldList = [];

    /**
     * @param array $fields
     * @return $this
     */
    public function addToFieldList(array $fields)
    {
        foreach ($fields as $field)
        {
            if(!in_array($field, $this->fieldList))
                $this->fieldList[] = $field;
        }
        return $this;
    }

    /**
     * @param $array
     * @return array
     */
    public function hideFields($array)
    {
        return collect($array)->forget($this->fieldList)->toArray();
    }

    /**
     * @param $array
     * @return array
     */
    public function restrictToFields($array)
    {
        if(isset($this->fieldList) and !empty($this->fieldList))
            return collect($array)->only($this->fieldList)->toArray();
        return $array;
    }
}
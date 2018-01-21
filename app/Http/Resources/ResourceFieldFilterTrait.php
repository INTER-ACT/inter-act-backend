<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 26.12.17
 * Time: 15:27
 */

namespace App\Http\Resources;


use Mockery\Exception;

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
                array_push($this->fieldList, $field);
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
        if(isset($this->fieldList) and !empty($this->fieldList)) {
            $new_array = [];
            foreach ($array as $key=>$item)
                if(in_array($key, $this->fieldList))
                    $new_array[$key] = $item;
            return $new_array;
        }
        return $array;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 16:05
 */

namespace App\Domain;


trait RepositoryFilterTrait
{
    /**
     * changes the values in $filters according to $change_values
     * moves values from $filters to $relations if they exist in $possible_relations
     *
     * @param array $filters
     * @param array $change_values
     * @param array $possible_relations
     * @param $relations
     * @return array
     */
    public function mapFilters(array $filters, array $change_values, array $possible_relations, &$relations)
    {
        $select_fields = array_map(function(&$item) use($change_values, $possible_relations, &$relations){
            if(array_key_exists($item, $change_values))
                $item = $change_values[$item];
            if(in_array($item, $possible_relations)) {
                array_push($relations, $item);
                return null;
            }
            return $item;
        }, $filters);
        return array_filter($select_fields, function($item){ return isset($item); });
    }
}
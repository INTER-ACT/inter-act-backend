<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 03.01.18
 * Time: 08:19
 */

namespace App\Http\Resources\StatisticsResources;


class ActionStatisticsResource
{
    /** @var  array */
    protected $data;

    /**
     * StatisticsResource constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray()
    {
        $header = [[
            'Typ',
            'Datum',
            'Geschlecht',
            'PLZ',
            'Job',
            'Höchster Bildungsabschluss',
            'Alter',
            'Beitrag',
            'Extra Information'
        ]];
        $out_array = array_merge($header, $this->data);

        return $out_array;
    }

    public static function transformCollectionToActionStatisticsResourceDataArray(string $type, \Illuminate\Database\Eloquent\Collection $collection)
    {
        return $collection->transform(function($item, $key) use($type){
            //return new ActionStatisticsResourceData($item->date, $item->user, $item->getResourcePath());
            $extra = ($item->ratable_rating_aspect === null) ? $item->extra : $item->ratable_rating_aspect->rating_aspect->name;
            return [$type, $item->date, $item->user->getSex(), $item->user->postal_code, $item->user->job, $item->user->graduation, $item->user->getAge(), $item->getResourcePath(), $extra];
        })->toArray();
    }
}
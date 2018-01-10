<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 08.01.18
 * Time: 11:45
 */

namespace App\Http\Resources\StatisticsResources;


use Illuminate\Database\Eloquent\Collection;

class RatingStatisticsResource extends CustomArrayResource
{
    /**
     * StatisticsResource constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $header = [
            'Datum',
            'Geschlecht',
            'PLZ',
            'Job',
            'Höchster Bildungsabschluss',
            'Alter',
            'Bewerteter Beitrag',
            'Rating-Aspect'
        ];
        parent::__construct($header, $data);
    }

    /**
     * @param string $type
     * @param Collection $collection
     * @return array
     */
    public static function transformCollectionToActionStatisticsResourceDataArray(string $type, Collection $collection)
    {
        return $collection->transform(function($item, $key) use($type){
            return [$item->date, $item->user->getSex(), $item->user->postal_code, $item->user->job, $item->user->graduation, $item->user->getAge(), $item->rated_post->getResourcePath(), $item->rated_post->getIdProperty(), $item->extra];
        })->toArray();
    }
}
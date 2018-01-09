<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 03.01.18
 * Time: 08:19
 */

namespace App\Http\Resources\StatisticsResources;


use App\Discussions\Discussion;
use App\Tags\Tag;

class ActionStatisticsResource extends CustomArrayResource
{
    /**
     * StatisticsResource constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $header = [
            'Diskussion/Tag',
            'Titel/Tag-Name'
        ];
        parent::__construct($header, $data);
    }

    public static function transformCollectionToActionStatisticsResourceDataArray(string $type, \Illuminate\Database\Eloquent\Collection $collection)
    {
        return $collection->transform(function($item, $key) use($type){
            $item_type = get_class($item);
            $title_name = ($item_type == Discussion::class) ? $item->title : ($item_type == Tag::class) ? $item->name : '-';
            return [$item->getResourcePath(), $title_name, $item->activity];
        })->toArray();
    }
}
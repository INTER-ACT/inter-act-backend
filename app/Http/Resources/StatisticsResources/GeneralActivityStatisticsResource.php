<?php

namespace App\Http\Resources\StatisticsResources;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\CommentRating;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Domain\EntityRepresentations\CommentRatingRepresentation;
use App\Domain\EntityRepresentations\MultiAspectRatingRepresentation;
use App\RatingAspectRating;
use App\Reports\Report;
use function foo\func;
use Illuminate\Database\Eloquent\Collection;
use Mockery\Exception;

class GeneralActivityStatisticsResource extends CustomArrayResource
{
    protected static $type_array = [Discussion::class => 'Diskussion', Amendment::class => 'Änderungsvorschlag', SubAmendment::class => 'Sub-Änderungsvorschlag', Comment::class => 'Kommentar', Report::class => 'Meldung', MultiAspectRatingRepresentation::class => 'Multi-Aspect-Rating', CommentRatingRepresentation::class => 'Kommentar-Bewertung'];

    /** @var  array */
    protected $data;

    /**
     * StatisticsResource constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $header = [
            'Typ',
            'Datum',
            'Geschlecht',
            'PLZ',
            'Job',
            'Höchster Bildungsabschluss',
            'Alter',
            'Beitrag',
            'Beitrags-ID',
            'Extra-Information'
        ];
        uasort($data, function($item1, $item2){
            return ($item1->getDate() == null) ? 1 : ($item2->getDate() == null) ? -1 : ($item1->getDate() == $item2->getDate()) ? 0 : ($item1->getDate() < $item2->getDate()) ? 1 : -1;
        });
        $data = array_map(function($item){
            return $item->toArray();
        }, $data);
        parent::__construct($header, $data);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function transformArrayToResourceDataArray(array $array)
    {
        array_map([self::class, 'EntityToResourceDataReturn'], $array);
        return $array;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @return array
     */
    public static function transformCollectionToResourceDataArray(Collection $collection)
    {
        return $collection->transform([self::class, 'EntityToResourceDataReturn'])->toArray();
    }

    /**
     * @param $item
     * @return GeneralActivityStatisticsResourceData
     */
    public static function EntityToResourceDataReturn($item)
    {
        if($item === null)
            throw new Exception('Item is null!');
        return new GeneralActivityStatisticsResourceData(self::$type_array[get_class($item)], $item->date, $item->user->getSex(), $item->user->postal_code, $item->user->job, $item->user->graduation, $item->user->getAge(), $item->getResourcePath(), $item->extra);
    }

    /**
     * @param $item
     * @param $key
     * @return void
     */
    public static function EntityToResourceDataChange(&$item, $key)
    {
        if($item === null)
            throw new Exception('Item for key ' . $key . ' is null!');
        foreach ($item as $element) {
            throw new Exception('Item: ' . $element);
        }

        $item = new GeneralActivityStatisticsResourceData(self::$type_array[get_class($item)], $item->date, $item->user->getSex(), $item->user->postal_code, $item->user->job, $item->user->graduation, $item->user->getAge(), $item->getResourcePath(), $item->extra);
    }
}

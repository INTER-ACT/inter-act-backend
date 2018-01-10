<?php

namespace App\Http\Resources\StatisticsResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class StatisticsResource extends CustomArrayResource
{
    /**
     * StatisticsResource constructor.
     * @param StatisticsResourceData $data
     */
    public function __construct(StatisticsResourceData $data)
    {
        $header = [
            'Anzahl Benutzer',
            'Durchschnittsalter Benutzer',
            'Anzahl männlicher Benutzer',
            'Anzahl weiblicher Benutzer',
            'Anzahl Diskussionen',
            'Anzahl Änderungsvorschläge',
            'Anzahl Sub-Änderungsvorschläge',
            'Anzahl Multiple-Aspect-Ratings',
            'Anzahl Kommentare',
            'Anzahl Kommentar-Bewertungen',
            'Anzahl Reports'
        ];
        $data_array = [
            $data->getUserCount(),
            $data->getAvgUserAge(),
            $data->getMaleUserCount(),
            $data->getFemaleUserCount(),
            $data->getDiscussionCount(),
            $data->getAmendmentCount(),
            $data->getSubAmendmentCount(),
            $data->getMaRatingCount(),
            $data->getCommentCount(),
            $data->getCommentRatingCount(),
            $data->getReportCount()
        ];
        parent::__construct($header, [$data_array]);
    }
}

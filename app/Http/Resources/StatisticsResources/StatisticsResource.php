<?php

namespace App\Http\Resources\StatisticsResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class StatisticsResource extends CustomArrayResource
{
    /** @var  StatisticsResourceData */
    protected $data;

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
            $this->data->getUserCount(),
            $this->data->getAvgUserAge(),
            $this->data->getMaleUserCount(),
            $this->data->getFemaleUserCount(),
            $this->data->getDiscussionCount(),
            $this->data->getAmendmentCount(),
            $this->data->getSubAmendmentCount(),
            $this->data->getMaRatingCount(),
            $this->data->getCommentCount(),
            $this->data->getCommentRatingCount(),
            $this->data->getReportCount()
        ];
        parent::__construct($header, [$data_array]);
    }
}

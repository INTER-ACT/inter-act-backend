<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 10.01.18
 * Time: 16:54
 */

namespace App\Http\Resources\StatisticsResources;


class CommentRatingStatisticsResource extends CustomArrayResource
{
    public function __construct(array $data)
    {
        $header = [
            'Kommentar',
            'positive Bewertungen',
            'negative Bewertungen',
            'Alter Q1 positive Bewerter',
            'Alter Median positive Bewerter',
            'Alter Q3 positive Bewerter',
            'Alter Q1 negative Bewerter',
            'Alter Median negative Bewerter',
            'Alter Q3 negative Bewerter',
            'Einstellung zum Thema (Sentiment)'
        ];
        parent::__construct($header, $data);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 09.01.18
 * Time: 08:42
 */

namespace App\Http\Resources\StatisticsResources;


class UserActivityStatisticsResource extends CustomArrayResource
{
    public function __construct(array $data)
    {
        $header = [
            'Benutzer',
            'Objekt',
            'Kurzbeschreibung',
            'Aktivität'
        ];
        parent::__construct($header, $data);
    }
}
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
use Illuminate\Database\Eloquent\Collection;

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
            'Titel/Name',
            'Aktivität Gesamt',
            'Aktivität letzter Monat'
        ];
        parent::__construct($header, $data);
    }
}
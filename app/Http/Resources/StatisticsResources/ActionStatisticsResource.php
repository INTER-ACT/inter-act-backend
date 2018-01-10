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
     * @param array $header
     * @param array $data
     */
    public function __construct(array $header, array $data)
    {
        parent::__construct($header, $data);
    }
}
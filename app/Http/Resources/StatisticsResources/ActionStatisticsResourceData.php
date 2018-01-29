<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 08.01.18
 * Time: 12:01
 */

namespace App\Http\Resources\StatisticsResources;


use App\IModel;
use Carbon\Carbon;

class ActionStatisticsResourceData
{
    /** @var string */
    protected $entity_path;
    /** @var string */
    protected $teaser;
    /** @var int */
    protected $activityTotal;
    /** @var int */
    protected $activityLastMonth;

    public function __construct(string $entity_path, string $teaser, int $activityTotal, int $activityLastMonth)
    {
        $this->entity_path = $entity_path;
        $this->teaser = $teaser;
        $this->activityTotal = $activityTotal;
        $this->activityLastMonth = $activityLastMonth;
    }

    public function toArray()
    {
        return [
            $this->entity_path,
            $this->teaser,
            $this->activityTotal,
            $this->activityLastMonth
        ];
    }
}
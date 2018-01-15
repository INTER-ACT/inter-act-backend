<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 14.01.18
 * Time: 13:15
 */

namespace App;


use Carbon\Carbon;

interface IHasActivity
{
    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return int
     */
    function getActivity(Carbon $start_date = null, Carbon $end_date = null) : int;
}
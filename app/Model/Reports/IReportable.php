<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 19:01
 */

namespace App\Reports;


use App\IModel;

interface IReportable extends IModel
{
    function reports();
}
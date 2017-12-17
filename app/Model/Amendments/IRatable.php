<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 19:50
 */

namespace App\Amendments;


use App\IModel;

interface IRatable extends IModel
{
    function ratings();
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 19:50
 */

namespace App\Amendments;

interface IRatable
{
    function ratings();

    function rating_sum();

    function user_rating();

    function getRatingSumAttribute();

    function getUserRatingAttribute();

    function getRatingPath();
}
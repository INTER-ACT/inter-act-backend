<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 18:49
 */

namespace App\Tags;

use App\IModel;

interface ITaggable extends IModel
{
    function tags();
}
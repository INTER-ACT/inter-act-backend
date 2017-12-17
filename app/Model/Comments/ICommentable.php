<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 21:20
 */

namespace App\Comments;


use App\IModel;

interface ICommentable extends IModel
{
    function comments();
}
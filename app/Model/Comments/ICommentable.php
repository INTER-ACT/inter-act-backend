<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 21:20
 */

namespace App\Comments;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface ICommentable
{
    /** @return MorphMany */
    function comments();
}
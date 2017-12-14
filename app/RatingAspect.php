<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RatingAspect extends Model
{
    public function ratings()
    {
        return $this->hasMany(RatableRatingAspect::class);
    }
}

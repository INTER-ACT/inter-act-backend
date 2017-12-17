<?php

namespace App\Amendments;

use App\IModel;
use Illuminate\Database\Eloquent\Model;

class RatingAspect extends Model implements IModel
{
    public function ratings()
    {
        return $this->hasMany(RatableRatingAspect::class);
    }

    public function amendments()
    {
        return $this->morphedByMany(Amendment::class, 'ratable');
    }

    public function subAmendments()
    {
        return $this->morphedByMany(SubAmendment::class, 'ratable');
    }

    function getIdProperty()
    {
        return $this->id;
    }
}

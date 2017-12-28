<?php

namespace App\Amendments;

use App\IModel;
use Illuminate\Database\Eloquent\Model;

class RatingAspect extends Model implements IModel
{
    //region IModel
    function getIdProperty()
    {
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }
    //endregion

    //region relations
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
    //endregion
}

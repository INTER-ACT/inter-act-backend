<?php

namespace App\Amendments;

use App\IModel;
use App\Model\RestModel;
use App\RatingAspectRating;
use Illuminate\Database\Eloquent\Model;

class RatingAspect extends RestModel
{
    protected $fillable = ['name'];

    public function getApiFriendlyType(): string
    {
        return 'rating_aspect';
    }

    //region relations
    public function ratings()
    {
        return $this->morphToMany(RatingAspectRating::class, 'ratable', 'ratable_rating_aspects');
    }

    public function ratable_rating_aspects()
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

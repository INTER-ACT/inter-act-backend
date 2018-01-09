<?php

namespace App;

use App\Amendments\RatableRatingAspect;
use Illuminate\Database\Eloquent\Model;

class RatingAspectRating extends Model implements IRestResource
{
    protected $table = "rating_aspect_rating";

    protected $fillable = [];

    /**
     * @return int
     */
    function getIdProperty()
    {
        return 0; //TODO: remove or implement
    }

    /**
     * @return string
     */
    function getType()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    function getResourcePath()
    {
        return '-';
        /*$rra = ($this->ratable_rating_aspect() === null) ? RatableRatingAspect::find($this->ratable_rating_aspect_id)->first(['id', 'discussion_id']) : $this->amendment;
        $this->loadMissing('ratable_rating_aspect');
        $rra = $this->ratable_rating_aspect->get();
        return $rra;
        return $rra->getResourcePath();*/
    }

    function user()
    {
        return $this->belongsTo(User::class);
    }

    function ratable_rating_aspect()
    {
        return $this->belongsTo(RatableRatingAspect::class, 'ratable_rating_aspect_id');
    }
}

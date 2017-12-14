<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubAmendment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendment()
    {
        return $this->belongsTo(Amendment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function ratings()
    {
        return $this->morphMany(RatableRatingAspect::class, 'ratable');
    }

    public function rating_aspects()
    {
        return $this->with('ratings.rating_aspect');    //TODO can be omitted --> same as Amendment::with('ratings.rating_aspect')
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->with('status', '=', $status);
    }
}

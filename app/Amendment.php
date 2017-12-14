<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Amendment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function sub_amendments()
    {
        return $this->hasMany(SubAmendment::class); //TODO: make sortable by status
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
}

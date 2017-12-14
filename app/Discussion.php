<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeActive($query)
    {
        return $query->where('status_message', '<>', '');
    }

    public function scopeInactive($query)
    {
        return $query->where('status_message', '<>', '');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public function taggables()
    {
        return $this->with(['discussions', 'amendments', 'sub_amendments']);    //Not sure if this works
    }

    public function discussions()
    {
        return $this->morphedByMany(Discussion::class, 'taggable');
    }

    public function amendments()
    {
        return $this->morphedByMany(Amendment::class, 'taggable');
    }

    public function sub_amendments()
    {
        return $this->morphedByMany(SubAmendment::class, 'taggable');
    }
}

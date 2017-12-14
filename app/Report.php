<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public function reportable()
    {
        return $this->morphTo('reportable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

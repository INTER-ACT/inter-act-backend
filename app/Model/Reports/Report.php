<?php

namespace App\Reports;

use App\IModel;
use Illuminate\Database\Eloquent\Model;

class Report extends Model implements IModel
{
    public function reportable()
    {
        return $this->morphTo('reportable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIdProperty()
    {
        return $this->id;
    }
}

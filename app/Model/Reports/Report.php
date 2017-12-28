<?php

namespace App\Reports;

use App\IModel;
use App\IRestResourceModel;
use Illuminate\Database\Eloquent\Model;

class Report extends Model implements IRestResourceModel
{
    //region IRestResourceModel
    public function getIdProperty()
    {
        return $this->id;
    }

    public function getResourcePath()
    {
        return '/reports/' . $this->id;
    }
    //endregion

    //region relations
    public function reportable()
    {
        return $this->morphTo('reportable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //endregion
}

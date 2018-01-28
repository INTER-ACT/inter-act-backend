<?php

namespace App\Reports;

use App\Model\RestModel;
use App\User;

class Report extends RestModel
{
    protected $fillable = ['explanation'];

    public function getApiFriendlyType() : string
    {
        return "report";
    }

    public function getApiFriendlyTypeGer() : string
    {
        return "Meldung";
    }

    public function getResourcePath() : string
    {
        return '/reports/' . $this->id;
    }

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

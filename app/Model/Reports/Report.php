<?php

namespace App\Reports;

use App\Model\RestModelPrimary;
use App\User;

class Report extends RestModelPrimary
{
    protected $fillable = ['explanation'];

    public function getApiFriendlyType() : string
    {
        return "report";
    }

    public function getResourcePath()
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

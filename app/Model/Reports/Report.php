<?php

namespace App\Reports;

use App\IModel;
use App\IRestResource;
use App\Model\RestModel;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Report extends RestModel
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

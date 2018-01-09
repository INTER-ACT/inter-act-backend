<?php

namespace App\Reports;

use App\IModel;
use App\IRestResource;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Report extends Model implements IRestResource
{
    protected $fillable = ['explanation'];

    //region IRestResource
    public function getIdProperty()
    {
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
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

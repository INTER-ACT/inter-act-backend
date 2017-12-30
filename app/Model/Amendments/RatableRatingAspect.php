<?php

namespace App\Amendments;

use App\IModel;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RatableRatingAspect extends Model implements IModel
{
    protected $fillable = [];

    //region IModel
    public function getIdProperty()
    {
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }
    //endregion

    //region relations
    public function ratable()
    {
        return $this->morphTo('ratable');
    }

    public function rating_aspect()
    {
        return $this->belongsTo(RatingAspect::class);
    }

    public function user_ratings()
    {
        return $this->belongsToMany(User::class, 'rating_aspect_rating');
    }
    //endregion

    public function scopeOfRatable($query, int $ratable_id, string $ratable_type)
    {
        return DB::table($this->table)
            ->where([['ratable_id', $ratable_id], ['ratable_type', $ratable_type]])
            ->get();

        /*return RatableRatingAspect::with(['ratable' => function($query) use($ratable_id, $ratable_type){
            return $query->where([['ratable_id', $ratable_id], ['ratable_type', $ratable_type]]);
        }]);*/
    }
}

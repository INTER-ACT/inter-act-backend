<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RatableRatingAspect extends Model
{
    public function ratable()
    {
        return $this->morphTo('ratable');
    }

    public function rating_aspect()
    {
        return $this->belongsTo(RatingAspect::class);
    }

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

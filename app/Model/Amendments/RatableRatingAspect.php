<?php

namespace App\Amendments;

use App\IHasActivity;
use App\IModel;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class RatableRatingAspect extends Model implements IModel, IHasActivity
{
    protected $table = "ratable_rating_aspects";

    protected $fillable = [];
    protected $appends = ['rating_sum'];

    //region IModel

    /**
     * @return int
     */
    public function getIdProperty()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return get_class($this);
    }
    //endregion

    //region Getters and Setters
    /**
     * @return int
     */
    public function getRatingSumAttribute()
    {
        return $this->rating_sum();
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return int
     */
    function getActivity(Carbon $start_date = null, Carbon $end_date = null): int
    {
        if(!isset($start_date)) {
            $start_date = now(2)->subMonths(3);
        }
        if(!isset($end_date)) {
            $end_date = now(2);
        }
        $rating_sum = DB::selectOne('SELECT count(*) as rating_sum from rating_aspect_rating ra
            WHERE ra.ratable_rating_aspect_id = :this_id
            AND ra.created_at > :start_date
            AND ra.created_at < :end_date
            GROUP BY ra.ratable_rating_aspect_id;', ["this_id" => $this->id, 'start_date' => $start_date->toDateTimeString(), 'end_date' => $end_date->toDateTimeString()]);
        return ($rating_sum === null) ? 0 : (int)$rating_sum->rating_sum;
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

    /**
     * @return int
     */
    public function rating_sum()
    {
        $rating_sum = DB::selectOne('SELECT count(*) as rating_sum from rating_aspect_rating ra
            WHERE ra.ratable_rating_aspect_id = :this_id
            GROUP BY ra.ratable_rating_aspect_id;', ["this_id" => $this->id]);
        return ($rating_sum === null) ? 0 : (int)$rating_sum->rating_sum;
    }

    public function user_ratings()
    {
        return $this->belongsToMany(User::class, 'rating_aspect_rating')->withTimestamps();
    }
    //endregion

    public function scopeOfRatable($query, int $ratable_id, string $ratable_type)
    {
        return $query->where([['ratable_id', $ratable_id], ['ratable_type', $ratable_type]])->get();

        /*return RatableRatingAspect::with(['ratable' => function($query) use($ratable_id, $ratable_type){
            return $query->where([['ratable_id', $ratable_id], ['ratable_type', $ratable_type]]);
        }]);*/
    }
}

<?php

namespace App\Discussions;

use App\Amendments\Amendment;
use App\Amendments\IRatable;
use App\Comments\Comment;
use App\Comments\ICommentable;
use App\IHasActivity;
use App\IModel;
use App\IRestResource;
use App\MultiAspectRating;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Discussion extends Model implements ITaggable, ICommentable, IRestResource, IHasActivity, IRatable
{
    use TTaggablePost;

    protected $fillable = ['title', 'law_text', 'law_explanation'];

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
        return '/discussions/' . $this->id;
    }
    //endregion

    //region Getters and Setters
    /*public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date);
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date);
    }*/ //TODO: Carbon or string returned for dates?

    /**
     * @return int
     */
    public function getActivityAttribute() : int
    {
        return $this->getActivity(Carbon::parse($this->created_at), now());
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return int
     */
    public function getActivity(Carbon $start_date = null, Carbon $end_date = null) : int
    {
        if(!isset($start_date)) {
            $start_date = now(2)->subMonths(3);
        }
        if(!isset($end_date)) {
            $end_date = now(2);
        }
        //$this->loadMissing(['comments', 'amendments']);
        $this->load(['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type');
        }]);
        $this->load(['amendments' => function($query){
            return $query->select('id', 'discussion_id');
        }]);
        $this->load(['ratings' => function($query){
            return $query->select(['*']);
        }]);
        $comment_sum = $this->comments->sum(function($comment) use($start_date, $end_date){
            return $comment->getActivity($start_date, $end_date);
        });
        $amendment_sum = $this->amendments->sum(function($amendment) use($start_date, $end_date){
            return $amendment->getActivity($start_date, $end_date);
        });
        $rating_sum = $this->ratings->count();
        return (int)($comment_sum + $amendment_sum + $rating_sum) + 1;
    }

    public function getRatingSumAttribute()
    {
        return $this->rating_sum();
    }

    public function getUserRatingAttribute()
    {
        return $this->user_rating();
    }

    public function getRatingPath()
    {
        return $this->getResourcePath() . '/rating';
    }
    //endregion

    //region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables');
    }

    public function ratings()
    {
        /*$thisTable = $this->getTable();
        $maTable = MultiAspectRating::TABLE_NAME;
        $queryResult = DB::table($thisTable)->select($maTable . '.*')->leftJoin($maTable, function(Builder $query) use($thisTable, $maTable) {
            $query->on($thisTable . '.id', '=', $maTable . '.ratable_id')
                ->where($maTable . '.ratable_type', '=', get_class($this));
        })->get();
        $ratings = [];
        foreach ($queryResult as $item)
        {
            array_push($ratings, new MultiAspectRating(json_decode(json_encode($item), true)));
        }
        return collect($ratings);*/
        return $this->morphMany(MultiAspectRating::class, 'ratable');
    }

    public function rating_sum()
    {
        return collect([
            MultiAspectRating::ASPECT1 => $this->ratings->sum(MultiAspectRating::ASPECT1),
            MultiAspectRating::ASPECT2 => $this->ratings->sum(MultiAspectRating::ASPECT2),
            MultiAspectRating::ASPECT3 => $this->ratings->sum(MultiAspectRating::ASPECT3),
            MultiAspectRating::ASPECT4 => $this->ratings->sum(MultiAspectRating::ASPECT4),
            MultiAspectRating::ASPECT5 => $this->ratings->sum(MultiAspectRating::ASPECT5),
            MultiAspectRating::ASPECT6 => $this->ratings->sum(MultiAspectRating::ASPECT6),
            MultiAspectRating::ASPECT7 => $this->ratings->sum(MultiAspectRating::ASPECT7),
            MultiAspectRating::ASPECT8 => $this->ratings->sum(MultiAspectRating::ASPECT8),
            MultiAspectRating::ASPECT9 => $this->ratings->sum(MultiAspectRating::ASPECT9),
            MultiAspectRating::ASPECT10 =>  $this->ratings->sum(MultiAspectRating::ASPECT10)
        ]);
    }

    public function user_rating()
    {
        return $this->ratings()->where('user_id', '=', \Auth::id())->first();
    }
    //endregion

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->archived_at == null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('archived_at');
    }
}

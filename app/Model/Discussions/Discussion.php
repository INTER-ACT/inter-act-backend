<?php

namespace App\Discussions;

use App\Amendments\Amendment;
use App\Comments\Comment;
use App\Comments\ICommentable;
use App\IHasActivity;
use App\IModel;
use App\IRestResource;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Discussion extends Model implements ITaggable, ICommentable, IRestResource, IHasActivity
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
        return '/discussions/' . $this->id; //TODO: Invalid if id is not selected
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
        $comment_sum = $this->comments->sum(function($comment) use($start_date, $end_date){
            return $comment->getActivity($start_date, $end_date);
        });
        $amendment_sum = $this->amendments->sum(function($amendment) use($start_date, $end_date){
            return $amendment->getActivity($start_date, $end_date);
        });
        return (int)($comment_sum + $amendment_sum) + 1;
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
        return $this->morphToMany(Tag::class, 'taggable');
    }
    //endregion

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('archived_at');
    }
}

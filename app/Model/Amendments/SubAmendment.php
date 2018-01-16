<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Exceptions\CustomExceptions\NotAcceptedException;
use App\IHasActivity;
use App\IModel;
use App\IRestResource;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class SubAmendment extends Model implements ITaggable, IReportable, IRatable, ICommentable, IRestResource, IHasActivity
{
    use TTaggablePost;

    public const PENDING_STATUS = 'pending';
    public const ACCEPTED_STATUS = 'accepted';
    public const REJECTED_STATUS = 'rejected';

    protected $fillable = ['updated_text', 'explanation'];

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
        //return (string)$this->amendment_id;
        $amendment = ($this->amendment === null) ? Amendment::find($this->amendment_id)->first(['id', 'discussion_id']) : $this->amendment;
        return $amendment->getResourcePath() . '/subamendments/' . $this->id;
    }
    //endregion

    //region Getters and Setters
    public function getChangesPath(){
        if($this->status != self::ACCEPTED_STATUS)
            throw new NotAcceptedException();

        return $this->amendment->getResourcePath() . "/changes/" . $this->amendment_version;
    }

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
    function getActivity(Carbon $start_date = null, Carbon $end_date = null): int
    {
        if(!isset($start_date)) {
            $start_date = now(2)->subMonths(3);
        }
        if(!isset($end_date)) {
            $end_date = now(2);
        }
        $relationsToLoad = ['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type');
        }, 'ratable_rating_aspects' => function($query){
                return $query->select('id', 'ratable_id', 'ratable_type');
        }];
        foreach ($relationsToLoad as $key => $item)
        {
            if($this->relationLoaded($key))
                unset($relationsToLoad[$key]);
        }
        $this->load($relationsToLoad);
        $comment_sum = $this->comments->sum(function($comment) use($start_date, $end_date){
            return $comment->getActivity($start_date, $end_date);
        });
        $rating_sum = $this->ratable_rating_aspects()->get()->sum(function($aspect) use($start_date, $end_date){
            return $aspect->getActivity($start_date, $end_date);
        });
        return (int)($comment_sum + $rating_sum) + 1;
    }
    //endregion

    //region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendment()
    {
        return $this->belongsTo(Amendment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function ratable_rating_aspects()
    {
        return $this->morphMany(RatableRatingAspect::class, 'ratable');
    }

    public function rating_aspects()
    {
        return $this->morphToMany(RatingAspect::class, 'ratable', 'ratable_rating_aspects');
    }

    /*public function ratings()
    {
        $this->loadMissing('ratable_rating_aspects:id');
        return $this->ratable_rating_aspects()->get()->transform(function($item){
            return $item->rating_sum;
        });
    }*/

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }
    //endregion

    public function scopeOfStatus($query, $status)
    {
        return $query->with('status', '=', $status);
    }
}

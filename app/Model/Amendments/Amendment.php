<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Discussions\Discussion;
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
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class Amendment extends Model implements ITaggable, IReportable, IRatable, ICommentable, IRestResource, IHasActivity
{
    use TTaggablePost;

    protected $fillable = ['updated_text', 'explanation'];

    //region IRestResource
    public function getIdProperty()
    {
        $this->getType();
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }

    public function getResourcePath()
    {
        $discussion_id = ($this->discussion_id === null) ? DB::selectOne('SELECT discussion_id from amendments WHERE id = ?', $this->id)->discussion_id : $this->discussion_id;
        return '/discussions/' . $discussion_id . '/amendments/' . $this->id;
    }
    //endregion

    //region Getters and Setters

    public function getChangesPath()
    {
        return $this->getResourcePath() . "/changes";
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
        $relationsToLoad = ['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type');
        }, 'sub_amendments' => function($query){
            return $query->select('id', 'amendment_id');
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
        $sub_amendment_sum = $this->sub_amendments->sum(function($sub_amendment) use($start_date, $end_date){
            return $sub_amendment->getActivity($start_date, $end_date);
        });
        $rating_sum = $this->ratable_rating_aspects()->get()->sum(function($aspect) use($start_date, $end_date){
            return $aspect->getActivity($start_date, $end_date);
        });
        return (int)($comment_sum + $sub_amendment_sum + $rating_sum) + 1;
    }
    //endregion

    //region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function sub_amendments()
    {
        return $this->hasMany(SubAmendment::class); //TODO: make sortable by status
    }

    public function changes()
    {
        return $this->sub_amendments()->where('status', '=', SubAmendment::ACCEPTED_STATUS);
    }

    public function rejections()
    {
        return $this->sub_amendments()->where('status', '=', SubAmendment::REJECTED_STATUS);
    }

    public function pending_sub_amendments()
    {
        return $this->sub_amendments()->where('status', '=', SubAmendment::PENDING_STATUS);
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
}

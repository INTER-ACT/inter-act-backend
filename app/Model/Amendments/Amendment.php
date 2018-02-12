<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Discussions\Discussion;
use App\IHasActivity;
use App\Model\RestModel;
use App\Model\RestModelPrimary;
use App\MultiAspectRating;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Amendment extends RestModel implements ITaggable, IReportable, IRatable, ICommentable, IHasActivity
{
    use TTaggablePost;

    protected $fillable = ['updated_text', 'explanation'];
    protected $appends = ['activity'];

    //region IRestResource
    public function getApiFriendlyType() : string
    {
        return "amendment";
    }

    public function getApiFriendlyTypeGer() : string
    {
        return "Änderungsvorschlag";
    }

    public function getResourcePath() : string
    {
        if(!array_key_exists('discussion_id', $this->attributes))
            $this->setAttribute('discussion_id', \DB::selectOne('SELECT discussion_id as id FROM amendments WHERE id = :this_id', ['this_id' => $this->id])->id);
        return '/discussions/' . $this->discussion_id . '/amendments/' . $this->id;
    }
    //endregion

    //region Getters and Setters

    public function getChangesPath()
    {
        return $this->getResourcePath() . "/changes";
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
    public function getActivity(Carbon $start_date = null, Carbon $end_date = null) : int
    {
        if(!isset($start_date)) {
            $start_date = now()->subYears(5);
        }
        if(!isset($end_date)) {
            $end_date = now();
        }
        if($this->created_at > $end_date)
            return 0;
        $relationsToLoad = ['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type', 'created_at');
        }, 'sub_amendments' => function($query){
            return $query->select('id', 'amendment_id', 'created_at');
        }, 'ratings' => function($query){
            return $query->select();
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
        $rating_sum = $this->ratings()->where([['created_at', '>=', $start_date],['created_at', '<=', $end_date]])->get()->count();
        $own = ($this->created_at >= $start_date) ? 1 : 0;
        return (int)($comment_sum + $sub_amendment_sum + $rating_sum) + $own;
    }

    public function getActivityBlacklisted(Carbon $start_date = null, Carbon $end_date = null, array &$amendment_blacklist, array &$sub_amendment_blacklist, array &$comment_blacklist) : int
    {
        if(in_array($this->id, $amendment_blacklist))
            return 0;
        else
            array_push($amendment_blacklist, $this->id);
        if(!isset($start_date)) {
            $start_date = now()->subYears(5);
        }
        if(!isset($end_date)) {
            $end_date = now();
        }
        if($this->created_at > $end_date)
            return 0;
        $relationsToLoad = ['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type', 'created_at');
        }, 'sub_amendments' => function($query){
            return $query->select('id', 'amendment_id', 'created_at');
        }, 'ratings' => function($query){
            return $query->select();
        }];
        foreach ($relationsToLoad as $key => $item)
        {
            if($this->relationLoaded($key))
                unset($relationsToLoad[$key]);
        }
        $this->load($relationsToLoad);
        $comment_sum = $this->comments->sum(function(Comment $comment) use($start_date, $end_date, &$comment_blacklist){
            return $comment->getActivityBlacklisted($start_date, $end_date, $comment_blacklist);
        });
        $sub_amendment_sum = $this->sub_amendments->sum(function(SubAmendment $sub_amendment) use($start_date, $end_date, &$sub_amendment_blacklist, &$comment_blacklist){
            return $sub_amendment->getActivityBlacklisted($start_date, $end_date, $sub_amendment_blacklist, $comment_blacklist);
        });
        //$sub_amendment_blacklist = array_merge($sub_amendment_blacklist, $this->sub_amendments->pluck('id')->all());
        //$comment_blacklist = array_merge($comment_blacklist, $this->comments->pluck('id')->all());
        $rating_sum = $this->ratings()->where([['created_at', '>=', $start_date],['created_at', '<=', $end_date]])->get()->count();
        $own = ($this->created_at >= $start_date) ? 1 : 0;
        return (int)($comment_sum + $sub_amendment_sum + $rating_sum) + $own;
    }

    public function getRatingSumAttribute()
    {
        return $this->rating_sum;
    }

    public function getUserRatingAttribute()
    {
        return $this->user_rating;
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

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function sub_amendments()
    {
        return $this->hasMany(SubAmendment::class);
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
        return $this->morphToMany(Tag::class, 'taggable', 'taggables');
    }

    public function ratings()
    {
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

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }
    //endregion
}

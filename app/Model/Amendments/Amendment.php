<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Discussions\Discussion;
use App\IHasActivity;
use App\IModel;
use App\IRestResource;
use App\MultiAspectRating;
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
    protected $appends = ['activity'];

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
        $relationsToLoad = ['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type');
        }, 'sub_amendments' => function($query){
            return $query->select('id', 'amendment_id');
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
        $rating_sum = $this->ratings()->count();
        return (int)($comment_sum + $sub_amendment_sum + $rating_sum) + 1;
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

    /*public function ratable_rating_aspects()
    {
        return $this->morphMany(RatableRatingAspect::class, 'ratable');
    }

    public function rating_aspects()
    {
        return $this->morphToMany(RatingAspect::class, 'ratable', 'ratable_rating_aspects', 'rating_aspect_id', 'ratable_id');
    }//TODO: not sure if it works if keys of pivot are not pk*/

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

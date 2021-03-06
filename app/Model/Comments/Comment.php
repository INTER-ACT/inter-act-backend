<?php

namespace App\Comments;

use App\CommentRating;
use App\IHasActivity;
use App\Model\RestModel;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\Tag;
use App\Traits\TPost;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Comment extends RestModel implements IReportable, ICommentable, IHasActivity
{
    use TPost;

    protected $fillable = ['content'];

    public function getApiFriendlyType() : string
    {
        return "comment";
    }

    public function getApiFriendlyTypeGer() : string
    {
        return "Kommentar";
    }

    public function getResourcePath() : string
    {
        return '/comments/' . $this->id;
    }

    //region Getters and Setters
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
    public function getActivity(Carbon $start_date = null, Carbon $end_date = null): int
    {
        if(!isset($start_date)) {
            $start_date = now()->subYears(5);
        }
        if(!isset($end_date)) {
            $end_date = now();
        }
        if($this->created_at > $end_date)
            return 0;
        $this->load(['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type', 'created_at');
        }]);
        $comment_sum = $this->comments->sum(function(Comment $comment) use($start_date, $end_date){
            return $comment->getActivity($start_date, $end_date);
        });
        $own = ($this->created_at >= $start_date) ? 1 : 0;
        return (int)($comment_sum) + $this->ratings()->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->count() + $own;
    }

    /**
     * @return array
     */
    public function getAllCommentIdsRecursive() : array
    {
        $ids = [$this->id];
        $this->comments->each(function(Comment $item) use(&$ids){
            $ids = array_merge($ids, $item->getAllCommentIdsRecursive());
        });
        return $ids;
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @param array $blacklist
     * @return int
     */
    public function getActivityBlacklisted(Carbon $start_date = null, Carbon $end_date = null, array &$blacklist) : int
    {
        if(in_array($this->id, $blacklist))
            return 0;
        else
            array_push($blacklist, $this->id);
        if(!isset($start_date)) {
            $start_date = now()->subYears(5);
        }
        if(!isset($end_date)) {
            $end_date = now();
        }
        if($this->created_at > $end_date)
            return 0;
        $this->load(['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type', 'created_at');
        }]);
        $comment_sum = $this->comments->sum(function(Comment $comment) use($start_date, $end_date, &$blacklist){
            return $comment->getActivityBlacklisted($start_date, $end_date, $blacklist);
        });
        //$blacklist = array_merge($blacklist, $this->comments()->pluck('id')->all());
        $own = ($this->created_at >= $start_date) ? 1 : 0;
        return (int)($comment_sum) + $this->ratings()->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->count() + $own;
    }

//    /**
//     * @return int|null
//     */
//    public function getUserRatingAttribute() : ?int
//    {
//        $user_id = \Auth::id();
//        return (isset($user_id)) ? $this->getUserRating($user_id) : null;
//    }
//
//    /**
//     * @param int $user_id
//     * @return int|null
//     */
//    public function getUserRating(int $user_id) : ?int
//    {
//        $selected_rating = DB::selectOne('SELECT cr.rating_score as rating FROM comments c JOIN comment_ratings cr on c.id = cr.comment_id JOIN users u on cr.user_id = u.id WHERE c.id = :this_id AND u.id = :user_id', ['this_id' => $this->id, 'user_id' => $user_id]);
//        return ($selected_rating == null) ? null : $selected_rating->rating;
//    }

    /**
     * @return int|null
     */
    public function getPositiveRatingCountAttribute() : ?int
    {
        return $this->positive_rating_count();
    }

    public function getNegativeRatingCountAttribute() : ?int
    {
        return $this->negative_rating_count();
    }

    public function getUserRatingAttribute() : ?int
    {
        $user_id = \Auth::id();
        if(!isset($user_id))
            return null;
        $rating = $this->ratings()->where('user_id', '=', \Auth::id())->first();
        return (isset($rating)) ? $rating->rating_score : null;
    }
    //endregion

    //region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()    //moved to commentable because the array key was commentable even in parent()-function
    {
        return $this->commentable();
    }

    public function commentable()
    {
        return $this->morphTo('commentable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function ratings()
    {
        return $this->hasMany(CommentRating::class, 'comment_id');
    }

    public function rating_users()
    {
        return $this->belongsToMany(User::class, 'comment_ratings', 'comment_id', 'user_id', 'id', 'id')->withTimestamps()->withPivot(['rating_score']);
    }

    public function positive_rating_count()
    {
        return $this->ratings()->where('rating_score', '>=', 1)->count();
    }

    public function negative_rating_count()
    {
        return $this->ratings()->where('rating_score', '<=', -1)->count();
    }
    //endregion

    public function scopeBetweenDates(Builder $query, Carbon $start_date, Carbon $end_date)
    {
        return $query->where('created_at', '>', $start_date)->where('created_at', '<', $end_date);
    }
}

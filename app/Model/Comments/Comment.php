<?php

namespace App\Comments;

use App\CommentRating;
use App\IHasActivity;
use App\IModel;
use App\IRestResource;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\Tag;
use App\Traits\TPost;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class Comment extends Model implements IReportable, ICommentable, IRestResource, IHasActivity
{
    use TPost;

    protected $fillable = ['content'];
    protected $appends = ['rating_sum', 'user_rating'];

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
        return '/comments/' . $this->id;
    }
    //endregion

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
            $start_date = now(2)->subMonths(3);
        }
        if(!isset($end_date)) {
            $end_date = now(2);
        }
        $this->load(['comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type');
        }]);
        $comment_sum = $this->comments->sum(function($comment) use($start_date, $end_date){
            return $comment->getActivity($start_date, $end_date);
        });
        return (int)($comment_sum) + $this->rating_sum() + 1;
    }

    public function getUserRatingAttribute() : ?int
    {
        $user_id = \Auth::id();
        return (isset($user_id)) ? $this->getUserRating($user_id) : null;
    }

    public function getUserRating(int $user_id) : ?int
    {
        $selected_rating = DB::selectOne('SELECT cr.rating_score as rating FROM comments c JOIN comment_ratings cr on c.id = cr.comment_id JOIN users u on cr.user_id = u.id WHERE c.id = :this_id AND u.id = :user_id', ['this_id' => $this->id, 'user_id' => $user_id]);
        return ($selected_rating == null) ? null : $selected_rating->rating;
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

    public function rating_users()  //TODO: change foreignPivotKey and relatedPivotKey for other Models as well if needed
    {
        return $this->belongsToMany(User::class, 'comment_ratings', 'comment_id', 'user_id')->withTimestamps()->withPivot(['rating_score']);
    }

    //returns the sum of all rating_scores related to this comment
    public function rating_sum()
    {//TODO update left join?
        $rating_sum = DB::selectOne('select sum(cr.rating_score) as rating_sum from users 
                                left join comment_ratings cr on users.id = cr.user_id
                                WHERE cr.comment_id = :comment_id
                                GROUP BY cr.comment_id;', ['comment_id' => $this->id]);
        return ($rating_sum === null) ? 0 : (int)$rating_sum->rating_sum;

        /*return $this->belongsToMany(User::class, 'comment_ratings', 'user_id', 'comment_id')
            ->selectRaw('sum(comment_ratings.rating_score) as rating_sum, count(comment_ratings.user_id) as rating_count')
            ->groupBy('pivot_comment_id');*/
    }

    public function getRatingSumAttribute()
    {
        return $this->rating_sum();
    }
    //endregion

    public function scopeBetweenDates(Builder $query, Carbon $start_date, Carbon $end_date)
    {
        return $query->where('created_at', '>', $start_date)->where('created_at', '<', $end_date);
    }
}

<?php

namespace App\Comments;

use App\IModel;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\Tag;
use App\Traits\TPost;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comment extends Model implements IModel, IReportable, ICommentable
{
    use TPost;

    protected $appends = ['rating_sum'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->morphTo('commentable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function rating_users()  //TODO: change foreignPivotKey and relatedPivotKey for other Models as well if needed
    {
        return $this->belongsToMany(User::class, 'comment_ratings', 'comment_id', 'user_id');
    }

    //returns the sum of all rating_scores related to this comment
    public function rating_sum()
    {
        $rating_sum = DB::selectOne('select sum(cr.rating_score) as rating_sum, count(cr.user_id) as rating_count from users 
                                left join comment_ratings cr on users.id = cr.user_id
                                WHERE cr.comment_id = :comment_id
                                GROUP BY cr.comment_id;', ['comment_id' => $this->id]);
        return ($rating_sum === null) ? 0 : $rating_sum;

        /*return $this->belongsToMany(User::class, 'comment_ratings', 'user_id', 'comment_id')
            ->selectRaw('sum(comment_ratings.rating_score) as rating_sum, count(comment_ratings.user_id) as rating_count')
            ->groupBy('pivot_comment_id');*/
    }

    public function getRatingSumAttribute()
    {
        return $this->rating_sum();//TODO: check null/etc. if needed (null-check done in function rating_sum() already
    }

    public function getIdProperty()
    {
        return $this->id;
    }
}

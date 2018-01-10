<?php

namespace App\Comments;

use App\CommentRating;
use App\IModel;
use App\IRestResource;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\Tag;
use App\Traits\TPost;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comment extends Model implements IReportable, ICommentable, IRestResource
{
    use TPost;

    protected $fillable = ['content'];
    protected $appends = ['rating_sum'];

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

    //region relations
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
    {
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
}

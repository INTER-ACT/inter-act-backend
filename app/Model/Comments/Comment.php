<?php

namespace App\Comments;

use App\IModel;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\Tag;
use App\Traits\TPost;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model implements IModel, IReportable, ICommentable
{
    use TPost;

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

    public function rating_users()
    {
        return $this->belongsToMany(User::class, 'comment_ratings', 'user_id', 'comment_id');
    }

    //returns the sum of all rating_scores related to this comment
    public function rating_sum()
    {
        return $this->belongsToMany(User::class, 'comment_ratings', 'user_id', 'comment_id')
            ->selectRaw('sum(comment_ratings.rating_score) as rating_sum')
            ->groupBy('comment_ratings.comment_id');    //Not sure if this is correct, maybe where checking that comment_id is id of this object
    }

    public function getIdProperty()
    {
        return $this->id;
    }
}

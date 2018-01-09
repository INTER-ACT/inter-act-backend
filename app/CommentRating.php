<?php

namespace App;

use App\Comments\Comment;
use Illuminate\Database\Eloquent\Model;

class CommentRating extends Model implements IRestResource
{
    protected $table = "comment_ratings";

    protected $fillable = ['rating_score'];

    /**
     * @return int
     */
    function getIdProperty()
    {
        return 0; //TODO: remove or implement
    }

    /**
     * @return string
     */
    function getType()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    function getResourcePath()
    {
        return '-';
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}

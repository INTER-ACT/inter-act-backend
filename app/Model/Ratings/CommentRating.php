<?php

namespace App;

use App\Comments\Comment;
use App\Model\RestModel;
use Illuminate\Database\Eloquent\Model;

class CommentRating extends RestModel
{
    //protected $primaryKey = ['user_id', 'comment_id'];    //TODO: update to composite primary key? (causes problems in collections)

    protected $table = "comment_ratings";

    protected $fillable = ['rating_score'];

    public function getId(): int
    {
        return null;
    }

    function getResourcePath()
    {
        return '/comments/' . $this->comment_id;
    }

    public function getApiFriendlyType(): string
    {
        return 'comment_rating';
    }

    public function getApiFriendlyRating() : string
    {
        $score = $this->rating_score;
        return ($score > 0) ? 'positiv' : (($score == 0) ? 'neutral' : 'negativ');
    }

    public function setRatingScoreAttribute(int $value)
    {
        $this->attributes['rating_score'] = ($value > 0) ? 1 : ($value == 0) ? 0 : -1;  //ceil(1/$value)
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

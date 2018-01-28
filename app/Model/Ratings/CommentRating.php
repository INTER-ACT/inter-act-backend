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

    function getResourcePath() : string
    {
        return '/comments/' . $this->comment_id;
    }

    public function getApiFriendlyType(): string
    {
        return 'comment_rating';
    }

    public function getApiFriendlyTypeGer(): string
    {
        return 'Kommentar-Bewertung';
    }

    public function getApiFriendlyRating() : string
    {
        $score = $this->getRatingScoreAttribute();
        return ($score > 0) ? 'positiv' : 'negativ';
    }

    public function setRatingScoreAttribute(int $value)
    {
        $this->attributes['rating_score'] = ($value > 0) ? true : false;
    }

    public function getRatingScoreAttribute() : int
    {
        return $this->attributes['rating_score'];
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

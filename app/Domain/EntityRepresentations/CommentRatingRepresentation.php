<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 08.01.18
 * Time: 10:21
 */

namespace App\Domain\EntityRepresentations;


use App\Comments\Comment;
use App\IRestResource;
use App\User;
use Carbon\Carbon;

class CommentRatingRepresentation implements IRestResource
{
    /** @var string */
    public $extra;
    /** @var string */
    public $date;
    /** @var Comment */
    public $comment;
    /** @var User */
    public $user;

    /**
     * CommentRatingRepresentation constructor.
     * @param string $created_at
     * @param Comment $comment
     * @param User $user
     * @param int $rating_score
     */
    public function __construct(string $created_at, Comment $comment, User $user, int $rating_score)
    {
        $this->date = $created_at;
        $this->comment = $comment;
        $this->user = $user;
        $this->extra = ($rating_score == 1) ? 'positiv' : 'negativ';
    }

    public function getResourcePath()
    {
        return "-";
    }

    public function getIdProperty()
    {
        return 0;
    }

    public function getType()
    {
        return get_class($this);
    }
}
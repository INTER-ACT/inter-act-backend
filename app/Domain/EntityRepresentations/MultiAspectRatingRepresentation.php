<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 08.01.18
 * Time: 10:25
 */

namespace App\Domain\EntityRepresentations;


use App\Amendments\IRatable;
use App\IRestResource;
use App\User;
use Carbon\Carbon;

class MultiAspectRatingRepresentation implements IRestResource
{
    /** @var string */
    public $extra;
    /** @var string */
    public $date;

    /** @var IRatable */
    public $rated_post;
    /** @var User */
    public $user;

    /**
     * MultiAspectRatingRepresentation constructor.
     * @param string $created_at
     * @param IRatable $rated_post
     * @param User $user
     * @param string $rating_aspect
     */
    public function __construct(string $created_at = null, IRatable $rated_post, User $user, string $rating_aspect)
    {
        $this->date = $created_at;
        $this->rated_post = $rated_post;
        $this->user = $user;
        $this->extra = $rating_aspect;
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
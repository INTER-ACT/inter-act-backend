<?php

namespace App\Http\Resources;

use App\Discussions\Discussion;
use App\MultiAspectRating;
use Illuminate\Http\Resources\Json\Resource;

class MultiAspectRatingResource extends ApiResource
{
    protected $discussion;

    /**
     * MultiAspectRatingResource constructor.
     * @param Discussion $discussion
     */
    public function __construct(Discussion $discussion)
    {
        $this->discussion = $discussion;
        parent::__construct($discussion);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = $this->getUrl($this->discussion->getRatingPath());
        return [
            'href' => $thisUrl,
            'user_rating' => $this->when(\Auth::check(), $this->getUserRatingRepresentation($this->discussion->user_rating)),
            'total_rating' => $this->discussion->rating_sum
        ];
    }

    protected function getUserRatingRepresentation(MultiAspectRating $user_rating = null)
    {
        return ($user_rating === null) ? MultiAspectRating::getEmptyRatingArray() : $user_rating;
    }
}

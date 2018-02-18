<?php

namespace App\Http\Resources;

use App\Amendments\IRatable;
use App\Discussions\Discussion;
use App\MultiAspectRating;
use Illuminate\Http\Resources\Json\Resource;

class MultiAspectRatingResource extends ApiResource
{
    protected $ratable;

    /**
     * MultiAspectRatingResource constructor.
     * @param IRatable $ratable
     */
    public function __construct(IRatable $ratable)
    {
        $this->ratable = $ratable;
        parent::__construct($ratable);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = $this->getUrl($this->ratable->getRatingPath());
        return [
            'href' => $thisUrl,
            'user_rating' => $this->when(\Auth::check(), $this->getUserRatingRepresentation($this->ratable->user_rating())),  // TODO move authentication out of this class
            'total_rating' => $this->ratable->rating_sum()
        ];
    }

    protected function getUserRatingRepresentation(MultiAspectRating $user_rating = null)
    {
        return ($user_rating === null) ? MultiAspectRating::getEmptyRatingArray() : $user_rating;
    }
}

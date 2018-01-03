<?php

namespace App\Http\Resources\RatingResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;
use phpDocumentor\Reflection\Types\Null_;

class CommentRatingResource extends Resource
{
    use RestResourceTrait;

    /**
     * Transform the rating of a comment into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request, $user=Null)
    {
        $thisUri = url($this->getResourcePathIfNotNull($this->getResourcePath()));

        if($user === Null)
            $userRating = Null;
        else
            $userRating = $this->rating_users()->where('user_id', '=', $user->id)->rating_score;

        return [
            'href' => $thisUri,
            'rating' => $this->rating_sum(),
            'user_rating' => $userRating
        ];
    }
}

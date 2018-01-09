<?php

namespace App\Http\Resources\RatingResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\DB;

class MultiAspectRatingResource extends Resource
{
    use RestResourceTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\User $user
     * @return array
     */
    public function toArray($request, $user=Null)
    {
        $thisUri = url($this->getResourcePathIfNotNull($this->getResourcePath()));

        // TODO get user ratings
        return [
            'href' => $thisUri,
            'ratings' => [],
            'user_ratings' => []
        ];
    }
}

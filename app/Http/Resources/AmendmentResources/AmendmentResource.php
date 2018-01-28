<?php

namespace App\Http\Resources\AmendmentResources;

use App\Http\Resources\ApiResource;

class AmendmentResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUri =  url($this->getResourcePathIfNotNull($this->getResourcePath()));
        $subamendmentUri = $thisUri . "/subamendments";
        $commentsUri = $thisUri . "/comments";
        $ratingsUri = $thisUri . "/ratings";
        $userRating = $thisUri . "/user_rating";


        $newest_amendment = $this->changes()->orderBy('handled_at', 'desc')->first();


        return [
            'href' => $thisUri,
            'id' => $this->id,
            'explanation' => $this->explanation,
            'changes' => [],                                        // TODO use Versioning system
            'version' => 1,      // TODO implement the correct version, after the amendment model has been altered
            'ratings' => [
                'href' => $ratingsUri
            ],
            'user_rating' => [
                'href' => $userRating
            ],
            'tags'=> $this->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            })->toArray(),
            'author' => [
                'href' => $this->user->getResourcePath(),
                'id' => $this->user->id
            ],
            'subamendments' => [
                'href' => $subamendmentUri
            ],
            'comments' => [
                'href' => $commentsUri
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String()
        ];
    }
}

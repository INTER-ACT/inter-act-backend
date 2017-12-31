<?php

namespace App\Http\Resources\SubAmendmentResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class SubAmendmentResource extends Resource
{
    use RestResourceTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = url($this->getResourcePathIfNotNull($this->getResourcePath()));

        $handled_at = $this->handled_at === Null ? Null : $this->handled_at->toIso8601String();

        return [
            'href' => $thisUrl,
            'id' => $this->id,
            'explanation' => $this->explanation,
            'created_at' => $this->created_at->toIso8601String(),
            'author' => [
                'href' => $this->user->getResourcePath(),
                'id' => $this->user->id
            ],
            'amendment_version' => $this->amendment_version,
            'ratings' => [
                'href' => $thisUrl . "/ratings"
            ],
            'user_rating' => [
                'href' => $thisUrl . "/user_rating"
            ],
            'tags' => $this->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            }),
            'changes' => [],         // TODO implement after versioning system has been implemented
            'comments' => [
                'href' => $thisUrl . "/comments"
            ],
            'status' => $this->status,
            'handled_at' => $handled_at,
            'handle_explanation' => $this->handle_explanation
        ];
    }
}

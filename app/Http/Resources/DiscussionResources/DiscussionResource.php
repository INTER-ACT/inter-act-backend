<?php

namespace App\Http\Resources\DiscussionResources;

use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class DiscussionResource extends Resource
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
        return [
            'href' => $thisUrl,
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at->toIso8601String(),   //Format: 1975-12-25T14:15:16-0500
            'updated_at' => $this->updated_at->toIso8601String(),   //TODO: change time format as described in the detailed api doc
            'law_text' => $this->law_text,
            'law_explanation' => $this->law_explanation,

            'author' => [
                'href' => url('/users/' . $this->user_id),
                'id' => $this->user_id
                ],
            'amendments' => ['href' => $thisUrl . '/amendments'],
            'comments' => ['href' => $thisUrl . '/comments']
        ];
    }
}

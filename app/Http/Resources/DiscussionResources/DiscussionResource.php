<?php

namespace App\Http\Resources\DiscussionResources;

use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use Illuminate\Http\Resources\Json\Resource;

class DiscussionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'href' => '/discussions/' . $this->id,
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at->toIso8601String(),   // 1975-12-25T14:15:16-0500
            'updated_at' => $this->updated_at->toIso8601String(),   //TODO: change time format as described in the detailed api doc
            'law_text' => $this->law_text,
            'law_explanation' => $this->law_explanation,

            'author' => [
                'href' => '/users/' . $this->user_id,
                'id' => $this->user_id
                ],
            'amendments' => new AmendmentCollection($this->amendments),
            'comments' => new CommentCollection($this->comments)
        ];
    }
}

<?php

namespace App\Http\Resources\CommentResources;

use App\Http\Resources\ResourceFieldFilterTrait;
use App\Http\Resources\TagCollection;
use Illuminate\Http\Resources\Json\Resource;

class CommentResource extends Resource
{
    use ResourceFieldFilterTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->restrictToFields([
            'href' => $request->path(),
            'id' => $this->id,
            'content' => $this->content,
            'created_at' => $this->created_at->toIso8601String(),   // 1975-12-25T14:15:16-0500
            'author' => [
                'href' => '/users/' . $this->user_id,
                'id' => $this->user_id
            ],
            'tags' => $this->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            }),
            'comments' => ['href' => $request->path() . '/comments'],
            'parent' => [
                'href' => null, //TODO: store resource path in resource (Model?)
                'id' => $this->parent->id
            ],
            'rating' => $this->rating_sum
        ]);
    }
}

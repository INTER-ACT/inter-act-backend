<?php

namespace App\Http\Resources\CommentResources;

use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\ResourceFieldFilterTrait;
use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class CommentResource extends Resource
{
    use RestResourceTrait;
    use ResourceFieldFilterTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($this->getResourcePath()));  //TODO: update href for list-resources since this way is incorrect when used in other resources (like search)
        return $this->restrictToFields([
            'href' => $thisURI,
            'id' => $this->id,
            'content' => $this->content,
            'created_at' => $this->created_at->toAtomString(),   // 1975-12-25T14:15:16-0500
            'updated_at' => $this->updated_at->toAtomString(),
            'author' => [
                'href' => url('/users/' . $this->user_id),
                'id' => $this->user_id
            ],
            'tags' => $this->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            }),
            'comments' => ['href' => $thisURI . '/comments'],
            'rating' => $this->rating_sum,
            'parent' => [
                'href' => url($this->parent->getResourcePath()),
                //'href' => $request->getUri() . $this->parent->getResourcePath(),
                'id' => $this->parent->id
                ]
        ]);
    }
}

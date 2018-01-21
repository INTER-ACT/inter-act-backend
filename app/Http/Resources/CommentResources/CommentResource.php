<?php

namespace App\Http\Resources\CommentResources;

use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\ResourceFieldFilterTrait;
use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;
use Mockery\Exception;

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
        $loadedAttributes = $this->getAttributes();
        return array_merge([
            'href' => $thisURI,
            'id' => $this->id
            ],
            $this->restrictToFields([
            'content' => $this->when(isset($this->content), $this->content),
            'created_at' => $this->when(array_key_exists('created_at', $loadedAttributes), function(){ return $this->created_at->toAtomString(); }),   // 1975-12-25T14:15:16-0500
            'author' => $this->when(array_key_exists('user_id', $loadedAttributes), [
                'href' => url('/users/' . $this->user_id),
                'id' => $this->user_id
            ]),
            'tags' => $this->when($this->relationLoaded('tags'), $this->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            })),
            'comments' => ['href' => $thisURI . '/comments'],
            'rating' => $this->rating_sum,
            'user_rating' => $this->when($this->user_rating, $this->user_rating),   //TODO: update in docs
            'parent' => $this->when($this->relationLoaded('commentable'), function(){ return [
                'href' => url($this->commentable->getResourcePath()),
                'id' => $this->commentable->id
                ];})
        ]));
    }
}

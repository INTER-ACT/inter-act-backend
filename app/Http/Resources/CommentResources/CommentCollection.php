<?php

namespace App\Http\Resources\CommentResources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'href' => $request->path(),
            //'total' => $this->collection->count(),
            'comments' => $this->collection->transform(function ($comment) use($request){
                return [
                    'href' => '/comments/' . $comment->id,
                    'id' => $comment->id
                ];
            })
        ];
    }
}

<?php

namespace App\Http\Resources\CommentResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    use RestResourceTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($request->path()));
        return [
            'href' => $thisURI,
            //'total' => $this->collection->count(),
            'comments' => $this->collection->transform(function ($comment) use($thisURI){
                return [
                    'href' => $thisURI . '/' . $comment->id,
                    'id' => $comment->id
                ];
            })
        ];
    }
}

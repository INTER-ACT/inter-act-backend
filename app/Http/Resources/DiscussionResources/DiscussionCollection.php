<?php

namespace App\Http\Resources\DiscussionResources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DiscussionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'href' => $request->path(),
            'total' => $this->collection->count(),
            'discussions' => $this->collection->transform(function ($discussion) use($request){
                return [
                    'href' => $request->path() . '/' . $discussion->id,
                    'id' => $discussion->id,
                    'title' => $discussion->title,
                    'article' => 'WAS IST ARTICLE???'   //TODO: update article-field
                ];
            })
        ];
    }
}

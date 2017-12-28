<?php

namespace App\Http\Resources\DiscussionResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DiscussionCollection extends ResourceCollection
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
        return[
            'href' => $thisURI,
            'total' => $this->collection->count(),
            'discussions' => $this->collection->transform(function ($discussion) use($thisURI){
                return [
                    'href' => $thisURI . '/' . $discussion->id,
                    'id' => $discussion->id,
                    'title' => $discussion->title,
                    'article' => 'WAS IST ARTICLE???'   //TODO: update article-field
                ];
            })
        ];
    }
}

<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollection extends ResourceCollection
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
        $thisURI = url($this->getResourcePathIfNotNull($request->getRequestUri()));
        return [
            'href' => $thisURI,
            'tags' => $this->collection->transform(function ($tag){
                return [
                    'href' => $tag->getResourcePath(),
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            })
        ];
    }
}

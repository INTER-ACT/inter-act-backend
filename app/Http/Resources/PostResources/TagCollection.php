<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

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
            'tags' => $this->getTagCollection()
        ];
    }

    public function toSubResourceArray()
    {
        return $this->getSubResourceTagCollection()->toArray();
    }

    private function getSubResourceTagCollection() : Collection
    {
        return $this->collection->transform(function ($tag){
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'description' => $tag->description
            ];
        });
    }

    private function getTagCollection() : Collection
    {
        return $this->collection->transform(function ($tag){
            return [
                'href' => $tag->getResourcePath(),
                'id' => $tag->id,
                'name' => $tag->name,
                'description' => $tag->description
            ];
        });
    }
}

<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\ApiCollectionResource;
use App\Tags\Tag;
use Illuminate\Support\Collection;

class TagCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($request->getRequestUri());

        return [
            'href' => $thisURI,
            'tags' => $this->getTagCollection()
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\JsonResponse $response
     */
    public function withResponse($request, $response)
    {
        $response->setStatusCode(200)
            ->header('Content-Type', 'text/json');
    }

    public function toSubResourceArray()
    {
        return $this->getSubResourceTagCollection()->toArray();
    }

    public static function getSubResourceItemArray(Tag $tag) : array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'description' => $tag->description
        ];
    }

    private function getSubResourceTagCollection() : Collection
    {
        return $this->collection->transform(function ($tag){
            return self::getSubResourceItemArray($tag);
        });
    }

    private function getTagCollection() : Collection
    {
        return $this->collection->transform(function ($tag){
            return [
                'href' => $this->getUrl($tag->getResourcePath()),
                'id' => $tag->id,
                'name' => $tag->name,
                'description' => $tag->description
            ];
        });
    }
}

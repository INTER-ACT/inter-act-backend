<?php

namespace App\Http\Resources\DiscussionResources;

use App\Http\Resources\ApiCollectionResource;

class DiscussionCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        $thisURI = url($request->getRequestUri());

        return[
            'href' => $thisURI,
            //'total' => $this->collection->count(),
            'discussions' => $this->collection->transform(function ($discussion){
                return [
                    'href' => $this->getUrl($discussion->getResourcePath()),
                    'id' => $discussion->id,
                    'title' => $discussion->title
                ];
            })
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
}

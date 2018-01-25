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
        $thisURI = url($this->getResourcePathIfNotNull($request->getRequestUri())); //TODO: change for search or leave it?
        return[
            'href' => $thisURI,
            //'total' => $this->collection->count(),
            'discussions' => $this->collection->transform(function ($discussion){
                return [
                    'href' => url($discussion->getResourcePath()),
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

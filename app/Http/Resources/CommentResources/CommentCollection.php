<?php

namespace App\Http\Resources\CommentResources;

use App\Http\Resources\ApiCollectionResource;

class CommentCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = url($request->getRequestUri());

        return [
            'href' => $thisUrl,
            //'total' => $this->collection->count(),
            'comments' => $this->collection->transform(function ($comment){
                return [
                    'href' => url($comment->getResourcePath()),
                    'id' => $comment->id
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

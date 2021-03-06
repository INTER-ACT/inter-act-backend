<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\ApiResource;

class TagResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = $this->getUrl($this->getResourcePath());
        return [
            'href' => $thisURI,
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
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

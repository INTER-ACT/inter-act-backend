<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\ApiResource;

class ReportResource extends ApiResource
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
            'user' => [
                'href' => $this->getUrl($this->user->getResourcePath()),
                'id' => $this->user->id
            ],
            'reported_item' => [
                'href' => $this->getUrl($this->reportable->getResourcePath()),
                'id' => $this->reportable->id,
                'type' => $this->reportable->getType()
            ],
            'description' => $this->explanation
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

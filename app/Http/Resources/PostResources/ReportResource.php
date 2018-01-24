<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class ReportResource extends Resource
{
    use RestResourceTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($this->getResourcePath()));
        return [
            'href' => $thisURI,
            'id' => $this->id,
            'user' => [
                'href' => url($this->user->getResourcePath()),
                'id' => $this->user->id
            ],
            'reported_item' => [
                'href' => url($this->reportable->getResourcePath()),
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

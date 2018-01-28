<?php

namespace App\Http\Resources;

use App\Discussions\Discussion;
use Illuminate\Http\Resources\Json\Resource;

class SuccessfulCreationResourceNoId extends ApiResource
{
    /**
     * SuccessfulCreationResourceNoId constructor.
     * @param Discussion $resource
     */
    public function __construct(Discussion $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'href' => $this->getUrl($this->getRatingPath())
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(201);
    }
}

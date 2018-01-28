<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class NoContentResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(204);
    }
}
<?php

namespace App\Http\Resources\UserResources;


use App\Http\Resources\ApiResource;

/**
 * Class SelfResource
 * Represents the id and href of a User
 *
 * @package App\Http\Resources\UserResources */
class SelfResource extends ApiResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = $this->customResourcePath . $this->getResourcePath();

        return [
            'href' => $thisURI,
            'id' => $this->id
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(200);
    }
}
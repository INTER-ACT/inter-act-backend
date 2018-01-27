<?php

namespace App\Http\Resources;

class SuccessfulCreationResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = $this->getUrl($this->getResourcePath());
        return [
            'href' => $thisUrl,
            'id' => $this->id
        ];
    }

    public function response($request = null)
    {
        return parent::response($request)->setStatusCode(201);
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(201);
    }
}

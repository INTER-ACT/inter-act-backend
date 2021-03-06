<?php

namespace App\Http\Resources\AmendmentResources;

use App\Http\Resources\ApiCollectionResource;

class AmendmentCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUri = url($request->getRequestUri());

        return [
            'href' => $thisUri,
            'amendments' => $this->collection->transform(function($amendment){
                return [
                    'href' => url($amendment->getResourcePath()),
                    'id' => $amendment->id
                ];
            })
        ];
    }
}

<?php

namespace App\Http\Resources\SubAmendmentResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubAmendmentCollection extends ResourceCollection
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
        $thisUri = url($this->getResourcePathIfNotNull($request->getRequestUri()));

        return [
            'href' => $thisUri,
            'subamendments' => $this->collection->transform(function($subamendment){
                return [
                    'href' => $subamendment->getResourcePath(),
                    'id' => $subamendment->id
                ];
            })

        ];
    }
}

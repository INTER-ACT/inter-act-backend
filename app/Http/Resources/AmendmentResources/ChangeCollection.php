<?php

namespace App\Http\Resources\AmendmentResources;

use App\Http\Resources\ApiCollectionResource;

class ChangeCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * Expects an Amendment as input
     * Returns a representation of the amendment's changes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $thisUri = url($this->getResourcePathIfNotNull($this->getChangesPath()));
        $thisUri = $this->getUrl($this->getChangesPath());

        return [
            'href' => $thisUri,
            'changes' => $this->changes->transform(function($change){
                return [
                    'href' => $this->getUrl($change->getChangesPath()),
                    'id' => $change->id,
                    'subamendment' => [
                        'href' => $this->getUrl($change->getResourcePath()),
                    ]
                ];
            })
        ];
    }
}

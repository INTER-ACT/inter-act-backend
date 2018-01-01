<?php

namespace App\Http\Resources\AmendmentResources;

use App\Amendments\Amendment;
use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChangeCollection extends Resource
{
    use RestResourceTrait;
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

        return [
            'href' => $thisUri,
            'changes' => $this->changes->transform(function($change){
                return [
                    'href' => url($change->getChangesPath()),
                    'id' => $change->id,
                    'subamendment' => [
                        'href' => url($change->getResourcePath()),
                    ]
                ];
            })
        ];
    }
}

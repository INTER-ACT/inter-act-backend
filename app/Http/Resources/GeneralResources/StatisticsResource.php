<?php

namespace App\Http\Resources\GeneralResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class StatisticsResource extends Resource
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
        $thisURI = url($this->getResourcePathIfNotNull($request->path()));
        return [
            'href' => $thisURI,
            
        ];
    }
}

<?php

namespace App\Http\Resources\AmendmentResources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AmendmentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}

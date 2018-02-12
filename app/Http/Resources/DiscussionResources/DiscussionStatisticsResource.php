<?php

namespace App\Http\Resources\DiscussionResources;

use App\Http\Resources\ApiResource;

class DiscussionStatisticsResource extends ApiResource
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
}

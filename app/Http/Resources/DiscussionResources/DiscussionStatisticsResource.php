<?php

namespace App\Http\Resources\DiscussionResources;

use Illuminate\Http\Resources\Json\Resource;

class DiscussionStatisticsResource extends Resource
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

<?php

namespace App\Http\Resources\UserResources;

use Illuminate\Http\Resources\Json\Resource;

class UserStatisticsResource extends Resource
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

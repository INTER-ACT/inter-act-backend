<?php

namespace App\Http\Resources\UserResources;

use App\Http\Resources\ApiResource;

class UserStatisticsResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($this->getResourcePath() . '/statistics'));
        return [
            'href' => $thisURI,
            'comments_count' => $this->comments->count(),
            'amendments_count' => $this->amendments->count(),
            'subamendments_count' => $this->sub_amendments->count()
        ];
    }
}

<?php

namespace App\Http\Resources\PostResources;

use Illuminate\Http\Resources\Json\Resource;

class ReportResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($request->path());
        return [
            'href' => $thisURI,
            'id' => $this->id,
            'user' => [
                'href' => url($this->user->getResourcePath()),
                'id' => $this->user->id
            ],
            'reported_item' => [
                'href' => url($this->reportable->getResourcePath()),
                'id' => $this->reportable->id,
                'type' => $this->reportable->getType()
            ],
            'description' => $this->explanation
        ];
    }
}

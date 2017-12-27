<?php

namespace App\Http\Resources\PostResources;

use Illuminate\Http\Resources\Json\Resource;

class TagResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'href' => $request->path(),
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
        ];
    }
}

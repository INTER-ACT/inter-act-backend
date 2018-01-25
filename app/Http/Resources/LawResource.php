<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class LawResource extends Resource
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
            'href' => $this->href,
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content
        ];
    }
}

<?php

namespace App\Http\Resources;

class LawResource extends ApiResource
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
            'href' => $this->getUrl($this->href),
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content
        ];
    }
}

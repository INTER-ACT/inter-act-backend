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
            'href' => $this->getUrl($this->getResourcePath()),
            'id' => $this->law_id,
            'articleParagraphUnit' => $this->articleParagraphUnit,
            'title' => $this->title,
            'content' => $this->content
        ];
    }
}

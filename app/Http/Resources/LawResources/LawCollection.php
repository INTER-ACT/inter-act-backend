<?php

namespace App\Http\Resources;

class LawCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'href' => url($request->getRequestUri()),
            'law_texts' => $this->collection->transform(function($item){
                return [
                    "id" => $item->id,
                    "href" => $this->getUrl($item->url),
                    "articleOrParagraph" => $item->articleParagraphUnit,
                    "title" => $item->title
                ];
            })
        ];
    }
}

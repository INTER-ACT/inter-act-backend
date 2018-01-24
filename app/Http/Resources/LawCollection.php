<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LawCollection extends ResourceCollection
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
                return $item->toArray();
            })
        ];
    }
}

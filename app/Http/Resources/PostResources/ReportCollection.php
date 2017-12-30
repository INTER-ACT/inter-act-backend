<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportCollection extends ResourceCollection
{
    use RestResourceTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($request->getRequestUri()));
        return [
            'href' => $thisURI,
            'reports' => $this->collection->transform(function ($report) use($thisURI){
                return [
                    'href' => url($report->getResourcePath()),
                    'id' => $report->id
                ];
            })
        ];
    }
}

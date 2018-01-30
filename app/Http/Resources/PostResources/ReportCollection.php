<?php

namespace App\Http\Resources\PostResources;

use App\Http\Resources\ApiCollectionResource;
use App\Reports\Report;

class ReportCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = $request->fullUrl();

        return [
            'href' => $thisURI,
            'reports' => $this->collection->transform(function(Report $report) use($thisURI){
                return [
                    'href' => $this->getUrl($report->getResourcePath()),
                    'id' => $report->id
                ];
            })
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\JsonResponse $response
     */
    public function withResponse($request, $response)
    {
        $response->setStatusCode(200)
            ->header('Content-Type', 'text/json');
    }
}

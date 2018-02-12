<?php

namespace App\Http\Resources\GeneralResources;

use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\ApiCollectionResource;
use App\Http\Resources\ApiResource;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;
use App\Model\IRestResource;
use App\Model\IRestResourcePrimary;
use App\Model\RestModel;

class SearchResource extends ApiCollectionResource
{
    /**
     * Transform the resource into an array. (resource has to be SearchResourceData)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($request->getRequestUri());

        return $this->getResponseArray($thisURI);
    }

    public function toArrayCustomUrl(string $url)
    {
        return $this->getResponseArray($url);
    }

    protected function getResponseArray(string $fullUrl)
    {
        return [
            'href' => $fullUrl,
            'search_results' => $this->collection->transform(function(IRestResource $item){
                return [
                    'href' => $this->getUrl($item->getResourcePath()),
                    'id' => $item->getId(),
                    'type' => $item->getApiFriendlyType()
                ];
            })->toArray()
        ];
    }
}

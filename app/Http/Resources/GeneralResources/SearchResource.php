<?php

namespace App\Http\Resources\GeneralResources;

use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;

class SearchResource extends ApiResource
{
    /**
     * SearchResource constructor.
     * @param array $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array. (resource has to be SearchResourceData)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($request->getRequestUri());

        return [
            'href' => $thisURI,
            'search_result' => $this->search_result
        ];
    }
}

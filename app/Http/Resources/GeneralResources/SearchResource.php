<?php

namespace App\Http\Resources\GeneralResources;

use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\RestResourceTrait;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;
use Illuminate\Http\Resources\Json\Resource;

class SearchResource extends Resource
{
    use RestResourceTrait;

    /**
     * Transform the resource into an array. (resource has to be SearchResourceData)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($request->getRequestUri()));
        return [
            'href' => $thisURI,
            'discussions' => new DiscussionCollection($this->getDiscussions()),
            'amendments' => new AmendmentCollection($this->getAmendments()),
            'subamendments' => new SubAmendmentCollection($this->getSubAmendments()),
            'comments' => new CommentCollection($this->getComments()),
        ];
    }
}

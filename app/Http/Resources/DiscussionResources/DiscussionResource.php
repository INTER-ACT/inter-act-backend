<?php

namespace App\Http\Resources\DiscussionResources;

use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\RestResourceTrait;
use Illuminate\Http\Resources\Json\Resource;

class DiscussionResource extends Resource
{
    use RestResourceTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = url($this->getResourcePathIfNotNull($this->getResourcePath()));
        return [
            'href' => $thisUrl,
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at->toAtomString(),   //Format: 1975-12-25T14:15:16-05:00
            'updated_at' => $this->updated_at->toAtomString(),   //TODO: change time format as described in the detailed api doc
            'law_text' => $this->law_text,
            'law_explanation' => $this->law_explanation,

            'author' => [
                'href' => url('/users/' . $this->user_id),
                'id' => $this->user_id
                ],
            'amendments' => ['href' => $thisUrl . '/amendments'],
            'comments' => ['href' => $thisUrl . '/comments'],
            'tags' => $this->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
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

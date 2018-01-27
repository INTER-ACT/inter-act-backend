<?php

namespace App\Http\Resources\CommentResources;

use App\Http\Resources\ApiResource;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\ResourceFieldFilterTrait;

class CommentResource extends ApiResource
{
    use ResourceFieldFilterTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUrl = $this->getUrl($this->getResourcePath());
        return [
            'href' => $thisUrl,
            'id' => $this->id,
            'content' => $this->content,
            'created_at' => $this->created_at->toAtomString(),
            'author' => [
                'href' => url($this->user->getResourcePath()),
                'id' => $this->user->id
            ],
            'tags' => (new TagCollection($this->tags))->toSubResourceArray(),
            'comments' => [
                'href' => url($this->getResourcePath() . '/comments')
            ],
            'parent' => [
                'href' => url($this->parent->getResourcePath()),
                'id' => $this->parent->id
            ],
            'positive_ratings' => $this->positive_rating_count,
            'negative_ratings' => $this->negative_rating_count,
            'user_rating' => $this->user_rating
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

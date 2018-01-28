<?php

namespace App\Http\Resources\UserResources;

use App\Http\Resources\ApiCollectionResource;

class UserCollection extends ApiCollectionResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($request->getRequestUri());

        return [
            'href' => $thisURI,
            'total' => $this->collection->count(),
            'users' => $this->collection->transform(function ($user){
                return [
                    'href' => $this->getUrl($user->getResourcePath()),
                    'id' => $user->id,
                    'username' => $user->username,
                ];
            })
        ];
    }
}

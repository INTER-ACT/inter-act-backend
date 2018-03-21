<?php

namespace App\Http\Resources;

use App\Discussions\Discussion;
use Illuminate\Http\Resources\Json\Resource;

class IndexResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'message' => 'Welcome to the Inter-Act API!',
            'home' => config('app.home_url')
        ];
    }
}

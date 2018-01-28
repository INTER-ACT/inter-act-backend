<?php

namespace App\Http\Resources\UserResources;

use App\Http\Resources\ApiResource;
use App\Role;


/**
 * Class ShortUserResource - converts only the public information of the User to json
 *
 * @package App\Http\Resources\UserResources
 */
class ShortUserResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisURI = url($this->getResourcePathIfNotNull($this->getResourcePath()));
        $role = Role::find($this->role_id);

        return [
            'href' => $thisURI,
            'id' => $this->id,
            'username' => $this->username,
            'role' => $role->name,
        ];
    }

}
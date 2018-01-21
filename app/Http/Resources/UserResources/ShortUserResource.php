<?php

namespace App\Http\Resources\UserResources;

use App\Http\Resources\RestResourceTrait;
use App\Role;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Routing\ResourceRegistrar;


/**
 * Class ShortUserResource - converts only the public information of the User to json
 *
 * @package App\Http\Resources\UserResources
 */
class ShortUserResource extends Resource
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
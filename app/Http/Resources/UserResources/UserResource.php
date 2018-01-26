<?php

namespace App\Http\Resources\UserResources;

use App\Http\Resources\RestResourceTrait;
use App\Role;
use Illuminate\Http\Resources\Json\Resource;

class UserResource extends Resource
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
        //$thisURI = url($this->getResourcePathIfNotNull($this->getResourcePath()));
        //$thisURI .= '/users/' . $this->id;
        $role = Role::find($this->role_id);
        $thisURI = $this->customResourcePath . $this->getResourcePath();
        return [
            'href' => $thisURI,
            'id' => $this->id,
            'username' => $this->username,
            'role' => $role->name,

            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'postal_code' => $this->postal_code,
            'residence' => $this->city,
            'job' => $this->job,
            'highest_education' => $this->graduation
        ];
    }
}

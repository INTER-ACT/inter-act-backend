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
        $thisURI = url($this->getResourcePathIfNotNull($this->getResourcePath()));
        $role = Role::find($this->role_id);

        return [
            'href' => $thisURI,
            'id' => $this->id,
            'username' => $this->username,
            'role' => $role->name,

            $this->mergeWhen(true, [        // TODO This should only be returned if the currently logged in user requests this information
                'email' => $this->email,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'postal_code' => $this->postal_code,
                'residence' => $this->city,
                'job' => $this->job,
                'highest_education' => $this->graduation
            ])
        ];
    }

}

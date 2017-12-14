<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    //TODO: UserPermission-Enum implementieren und dokumentieren --> erst in Domain-Schicht?
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Role extends Model implements IModel
{
    const ADMIN_NAME = 'admin';
    const EXPERT_NAME = 'expert';
    const ANALYST_NAME = 'analyst';
    const STANDARD_USER_NAME = 'standard_user';
    const GUEST_NAME = 'guest';

    //TODO: Seed Roles, Permissions, Tags, ... already at application boot?
    //region static entries
    public static function getAdmin()
    {
        return Role::CreateRoleIfNotExists(Role::ADMIN_NAME, [Permission::getAdministrate(), Permission::getCreateExpertExplanations(), Permission::getAnalyze(),Permission::getCreateDiscussions(), Permission::getCreateArticles(), Permission::getRead()]);
    }

    public static function getExpert()
    {
        return Role::CreateRoleIfNotExists(Role::EXPERT_NAME, [Permission::getCreateExpertExplanations(), Permission::getCreateArticles(), Permission::getRead()]);
    }

    public static function getAnalyst()
    {
        return Role::CreateRoleIfNotExists(Role::ANALYST_NAME, [Permission::getAnalyze(), Permission::getCreateArticles(), Permission::getRead()]);
    }

    public static function getStandardUser()
    {
        return Role::CreateRoleIfNotExists(Role::STANDARD_USER_NAME, [Permission::getCreateArticles(), Permission::getRead()]);
    }

    public static function getGuest()
    {
        return Role::CreateRoleIfNotExists(Role::GUEST_NAME, [Permission::getRead()]);
    }
    //endregion

    //region IModel
    function getIdProperty()
    {
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }
    //endregion

    //region relations
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    //endregion

    private static function CreateRoleIfNotExists(string $name, array $permissions)
    {
        $role = Role::where('name', '=', $name)->first();
        if($role === null) {
            $role = Role::create(['name' => $name]);
            if($permissions) {
                foreach ($permissions as $permission)
                    $role->permissions()->attach($permission->id);  //TODO: improve efficiency
            }
        }
        return $role;
    }
}

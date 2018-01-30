<?php

namespace App;

use App\Model\RestModel;
use Exception;

class Role extends RestModel
{
    const ADMIN_NAME = 'admin';
    const EXPERT_NAME = 'expert';
    const SCIENTIST_NAME = 'scientist';
    const STANDARD_USER_NAME = 'standard_user';
    const GUEST_NAME = 'guest';

    protected $fillable = [];

    protected static function boot()
    {
        parent::boot();
        self::createBaseRoles();
    }

    //region static entries
    /**
     * @return Role
     */
    public static function getAdmin() : Role
    {
        return Role::where('name', '=', self::ADMIN_NAME)->first();
    }

    /**
     * @return Role
     */
    public static function getExpert() : Role
    {
        return Role::where('name', '=', self::EXPERT_NAME)->first();
    }

    /**
     * @return Role
     */
    public static function getScientist() : Role
    {
        return Role::where('name', '=', self::SCIENTIST_NAME)->first();
    }

    /**
     * @return Role
     */
    public static function getStandardUser() : Role
    {
        return Role::where('name', '=', self::STANDARD_USER_NAME)->first();
    }

    /**
     * @return Role
     */
    public static function getGuest() : Role
    {
        return Role::where('name', '=', self::GUEST_NAME)->first();
    }
    //endregion

    public function getApiFriendlyType(): string
    {
        return 'role';
    }

    public function getApiFriendlyTypeGer(): string
    {
        return 'Rolle';
    }

    public function hasPermission(Permission $permission)
    {
        return $this->permissions()->where('id', '=', $permission->id)->count() >= 0;
    }

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

    /**
     * return @void
     */
    public static function createBaseRoles()
    {
        Role::CreateRoleIfNotExists(Role::ADMIN_NAME, [Permission::getAdministrate(), Permission::getCreateExpertExplanations(), Permission::getAnalyze(),Permission::getCreateDiscussions(), Permission::getCreateArticles(), Permission::getRead()]);
        Role::CreateRoleIfNotExists(Role::EXPERT_NAME, [Permission::getCreateExpertExplanations(), Permission::getCreateArticles(), Permission::getRead()]);
        Role::CreateRoleIfNotExists(Role::SCIENTIST_NAME, [Permission::getAnalyze(), Permission::getCreateArticles(), Permission::getRead()]);
        Role::CreateRoleIfNotExists(Role::STANDARD_USER_NAME, [Permission::getCreateArticles(), Permission::getRead()]);
        Role::CreateRoleIfNotExists(Role::GUEST_NAME, [Permission::getRead()]);
    }

    /**
     * @param string $name
     * @param array $permissions
     * @return Role
     */
    private static function CreateRoleIfNotExists(string $name, array $permissions) : Role
    {
        $role = Role::where('name', '=', $name)->first();
        if($role === null) {
            $role = new Role();
            $role->name = $name;
            $role->save();
            if($permissions) {
                foreach ($permissions as $permission)
                    $role->permissions()->attach($permission->id);  //TODO: improve efficiency
            }
        }
        return $role;
    }

    /**
     * @param $roleName
     * @return Role
     * @throws Exception
     */
    public static function getRoleByName($roleName) : Role
    {
        if($roleName == self::ADMIN_NAME)
            return self::getAdmin();

        if($roleName == self::EXPERT_NAME)
            return self::getExpert();

        if($roleName == self::SCIENTIST_NAME)
            return self::getScientist();

        if($roleName == self::STANDARD_USER_NAME)
            return self::getStandardUser();

        if($roleName == self::GUEST_NAME)
            return self::getGuest();

        else
            throw new Exception("The Role $roleName does not exist.");
    }

    /**
     * Returns all possible Role names
     *
     * @return array
     */
    public static function getAllRoleNames()
    {
        return [
            self::ADMIN_NAME,
            self::SCIENTIST_NAME,
            self::STANDARD_USER_NAME,
            self::GUEST_NAME,
            self::EXPERT_NAME
        ];
    }
}

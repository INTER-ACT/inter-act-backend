<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model implements IModel
{
    //region constants
    const READ_NAME = 'read';
    const CREATE_ARTICLES_NAME = 'create_articles';  //TODO: imo it should be called posts instead of articles, but would have to be changed in documentation
    const CREATE_DISCUSSIONS_NAME = 'create_discussions';
    const ANALYZE_NAME = 'analyze';
    const CREATE_EXPERT_EXPLANATIONS_NAME = 'create_expert_explanations';
    const ADMINISTRATE_NAME = 'administrate';
    //endregion

    protected $fillable = ['name'];

    //region static_entries
    public static function getRead()
    {
        return Permission::firstOrCreate(['name' => Permission::READ_NAME]);
    }

    public static function getCreateArticles()
    {
        return Permission::firstOrCreate(['name' => Permission::CREATE_ARTICLES_NAME]);
    }

    public static function getCreateDiscussions()
    {
        return Permission::firstOrCreate(['name' => Permission::CREATE_DISCUSSIONS_NAME]);
    }

    public static function getAnalyze()
    {
        return Permission::firstOrCreate(['name' => Permission::ANALYZE_NAME]);
    }

    public static function getCreateExpertExplanations()
    {
        return Permission::firstOrCreate(['name' => Permission::CREATE_EXPERT_EXPLANATIONS_NAME]);
    }

    public static function getAdministrate()
    {
        return Permission::firstOrCreate(['name' => Permission::ADMINISTRATE_NAME]);
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
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_roles');
    }
    //endregion
}

<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mockery\Exception;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    public function subAmendments()
    {
        return $this->hasMany(SubAmendment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings()
    {
        throw new Exception("User Ratings not implemented!");   //TODO: Implement User->ratings
        //return $this->hasMany(RatableRatingAspect::class);
    }

    public function rated_comments()
    {
        return $this->belongsToMany(Comment::class)
            ->withTimestamps()
            ->withPivot('rating_score');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function reported_amendments()
    {
        return $this->morphedByMany(Amendment::class, 'reportable');
    }

    public function reported_subamendments()
    {
        return $this->morphedByMany(SubAmendment::class, 'reportable');
    }

    public function reported_comments()
    {
        return $this->morphedByMany(Comment::class, 'reportable');
    }

    //TODO: change in documentation (was planned as scope)
    public static function usersOfRole(Role $role)
    {
        return User::with('role')->where('role.role_name', '=', $role->name)->get();
    }

    //TODO: change in documentation (was planned as scope)
    public static function usersWithPermission(string $permission)  //TODO: enum instead of string
    {
        return User::with(['role.permissions' => function($query) use ($permission){
            $query->where('name', $permission);
        }]);

        /*$users = User::with('role.permissions')->get();
        $users->filter(function($user){
           return $user->role()->permissions()->contains('name', '$permission');
        });*/
    }
}

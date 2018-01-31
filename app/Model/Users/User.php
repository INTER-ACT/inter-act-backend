<?php

namespace App;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Model\AuthRestModel;
use App\Reports\Report;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends AuthRestModel
{
    use Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'first_name', 'last_name', 'is_male', 'postal_code', 'city', 'job', 'graduation', 'year_of_birth'
    ];//TODO: should email and password be mass-assignable?

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pending_password', 'pending_token'
    ];

    public function getSex(){
        return $this->is_male ? 'm' : 'f';
    }

    public function getResourcePath() : string
    {
        return '/users/' . $this->id;
    }

    public function getApiFriendlyType(): string
    {
        return 'user';
    }

    public function getApiFriendlyTypeGer(): string
    {
        return 'Benutzer';
    }

    //region relations
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

    public function sub_amendments()
    {
        return $this->hasMany(SubAmendment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings()
    {
        return $this->hasMany(MultiAspectRating::class, 'user_id');
    }

    public function rated_comments()
    {
        return $this->belongsToMany(Comment::class, 'comment_ratings', 'user_id', 'comment_id')
            ->withTimestamps()
            ->withPivot('rating_score');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id', 'id');
    }
    //endregion

    /**
     * @param Role $role
     * @return bool
     */
    public function hasRole(Role $role) : bool
    {
        return $this->role->id == $role->id;
    }

    /**
     * @param Permission $permission
     * @return bool
     */
    public function hasPermission(Permission $permission) : bool
    {
        return $this->role->hasPermission($permission);
    }

    //TODO: change in documentation (was planned as scope)
    public static function ofRole(Role $role)
    {
        return User::with('role')->where('role.role_name', '=', $role->name)->get();
    }

    //TODO: change in documentation (was planned as scope)
    public static function withPermission(string $permission)  //TODO: enum instead of string
    {
        return User::with(['role.permissions' => function($query) use ($permission){
            $query->where('name', $permission);
        }]);

        /*$users = User::with('role.permissions')->get();
        $users->filter(function($user){
           return $user->role()->permissions()->contains('name', '$permission');
        });*/
    }

    /**
     * @return string
     */
    public function getFullName() : string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return int
     */
    public function getAge() : int
    {
        return Carbon::now()->year - $this->year_of_birth;
    }

    /**
     * @return string
     */
    public function getVerificationUrl() : string
    {
        return self::getVerificationUrlForToken($this->pending_token);
    }

    /**
     * @param string $validation_token
     * @return string
     */
    public static function getVerificationUrlForToken(string $validation_token) : string
    {
        return config('app.url') . '/update_password/' . $validation_token;
    }

    /**
     * @param string $password
     * @return string
     */
    public static function getNewToken(string $password) : string
    {
        return sha1(uniqid($password, true));
    }

    /**
     * Returns true, if the role has the given permission
     *
     * @param Permission $permission
     * @return bool
     */
    public function checkPermission(Permission $permission)
    {
        return $this->role->permissions()->where('name', '=', $permission->name)->exists();
    }
}

<?php

namespace App;

use App\Exceptions\CustomExceptions\InternalServerError;
use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    public $incrementing = false;

    protected $fillable = ['password', 'username', 'email', 'first_name', 'last_name', 'is_male', 'postal_code', 'city', 'job', 'graduation', 'year_of_birth'];
    protected $primaryKey = 'validation_token';

    /**
     * @return string
     */
    public function getVerificationUrl() : string
    {
        return self::getVerificationUrlForToken($this->validation_token);
    }

    public static function getVerificationUrlForToken(string $validation_token) : string
    {
        return config('app.url') . '/verify_user/' . $validation_token;
    }

    public static function getNewToken(string $username) : string
    {
        return sha1(uniqid($username, true));
    }

    public function getUser() : User
    {
        $user = new User();
        $user->role_id = Role::getStandardUser()->id;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->password = $this->password;
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->is_male = $this->is_male;
        $user->postal_code = $this->postal_code;
        $user->city = $this->city;
        $user->job = $this->job;
        $user->graduation = $this->graduation;
        $user->year_of_birth = $this->year_of_birth;
        return $user;
    }
}

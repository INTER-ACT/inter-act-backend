<?php

namespace App\Domain\Manipulators;


use App\Discussions\Discussion;
use App\Domain\UserRepository;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Mail\VerifyPasswordUpdate;
use App\Mail\VerifyUser;
use App\PendingUser;
use App\Role;
use App\User;
use Hash;
use Mail;

class UserManipulator
{
    /**
     * @param array $data
     * @return PendingUser
     * @throws InternalServerError
     */
    public static function create(array $data) : PendingUser
    {
        $user = new PendingUser();
        $user->validation_token = PendingUser::getNewToken($data['username']);
        $user->fill($data);
        $user->is_male = $data['sex'] == 'm';
        $user->city = $data['residence'];
        $user->graduation = $data['highest_education'];
        $user->password = Hash::make($data['password']);

        if(!$user->save())
            throw new InternalServerError('The User could not be saved.');

        Mail::send(new VerifyUser($user));
        return $user;
    }

    /**
     * @param string $token
     * @return User
     * @throws InternalServerError
     */
    public static function verifyUser(string $token) : User
    {
        /** @var PendingUser $pendingUser */
        $pendingUser = PendingUser::find($token);
        if(!isset($pendingUser))
            throw new InternalServerError('The given token is not valid.');
        $user = $pendingUser->getUser();
        if(!$user->save())
            throw new InternalServerError('Could not verify the user.');
        $pendingUser->delete();
        return $user;
    }

    /**
     * @param int $id
     * @param array $data
     * @throws InternalServerError
     */
    public static function update(int $id, array $data)
    {
        /** @var User $user */
        $user = UserRepository::getByIdOrThrowError($id);
        $user->fill($data);
        if(isset($data['password']))
            $user->password = Hash::make($data['password']);
        if(!$user->save())
            throw new InternalServerError("The User $id could not be updated.");
        //Mail::send(new VerifyPasswordUpdate($user));
    }

    /*public static function verifyPasswordUpdate(string $token) : User
    {
        $user = User::where('pending_token', '=', $token)->first();
        if(!isset($user))
            throw new InternalServerError('Password update failed: token not valid.');
        if(!isset($user->pending_password))
            throw new InternalServerError('Password update failed: no updated password available.');
        $user->password = $user->pending_password;
        $user->pending_password = null;
        $user->pending_token = null;
        if(!$user->save())
            throw new InternalServerError("Could not update the password");
        return $user;
    }*/

    public static function delete(int $id)
    {
        $user = UserRepository::getByIdOrThrowError($id);

        if(!$user->delete())
            throw new InternalServerError("The User $id couldn't be deleted");
    }

    public static function updateRole(int $id, UpdateUserRoleRequest $roleName)
    {
        $user = UserRepository::getByIdOrThrowError($id);

        $user->role_id = Role::getRoleByName($roleName->getData()['role'])->id;

        if(!$user->update())
            throw new InternalServerError("The Role of User $id could not be updated.");
    }

    public static function updatePassword(int $id, string $password)
    {
        $user = UserRepository::getByIdOrThrowError($id);

        $user->password = Hash::make($password);

        if(!$user->update())
            throw new InternalServerError("User $id could not be updated.");
    }
}
<?php

namespace App\Domain\Manipulators;


use App\Discussions\Discussion;
use App\Domain\User\UserRepository;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Role;
use App\User;
use Hash;

class UserManipulator
{
    /**
     * @param array $data
     * @return User
     * @throws InternalServerError
     */
    public static function create(array $data)
    {
        $user = new User();

        // TODO move this to a more appropriate place

        $user->fill($data);
        $user->role_id = Role::getStandardUser()->id;
        $user->is_male = $data['sex'] == 'm';
        $user->city = $data['residence'];
        $user->graduation = $data['highest_education'];
        $user->password = Hash::make($data['password']);

        if(!$user->save())
            throw new InternalServerError('The User could not be saved.');

        return $user;
    }

    /**
     * @param int $id
     * @param array $data
     * @throws InternalServerError
     */
    public static function update(int $id, array $data)
    {
        $user = UserRepository::getByIdOrThrowError($id);

        if(!$user->update($data))
            throw new InternalServerError("The User $id couldn't be updated.");
    }

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
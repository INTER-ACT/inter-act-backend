<?php

namespace App\Policies;

use App\Discussions\Discussion;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Role;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Log;

class DiscussionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the discussion.
     *
     * @param  \App\User $user
     * @param  Discussion $discussion
     * @return mixed
     * @throws NotPermittedException
     */
    public function view(User $user, Discussion $discussion)
    {
        if(!$discussion->isActive() and (!isset($user) or !$user->hasRole(Role::getAdmin())))
            throw new NotPermittedException("Archived Discussions can only be fetched by Administrators.");
        return true;
    }

    /**
     * Determine whether the user can create discussions.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole(Role::getAdmin());
    }

    /**
     * Determine whether the user can update the discussion.
     *
     * @param  \App\User  $user
     * @param  Discussion  $discussion
     * @return mixed
     */
    public function update(User $user, Discussion $discussion)
    {
        return $user->hasRole(Role::getAdmin());
    }

    /**
     * Determine whether the user can delete the discussion.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->hasRole(Role::getAdmin());
    }
}

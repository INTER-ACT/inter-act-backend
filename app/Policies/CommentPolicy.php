<?php

namespace App\Policies;

use App\Comments\Comment;
use App\Role;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the comment.
     *
     * @param  \App\User  $user
     * @param  Comment  $comment
     * @return mixed
     */
    public function view(User $user, Comment $comment)
    {
        return true;
    }

    /**
     * Determine whether the user can create comments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return !$user->hasRole(Role::getGuest());
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param  \App\User  $user
     * @param  Comment  $comment
     * @return mixed
     */
    public function update(User $user, Comment $comment)
    {
        return $comment->user_id == $user->id;  //TODO admins should be able to delete or update
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param  \App\User  $user
     * @param  Comment  $comment
     * @return mixed
     */
    public function delete(User $user, Comment $comment)
    {
        return false;
    }
}

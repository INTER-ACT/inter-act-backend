<?php

namespace App\Policies;

use App\Reports\Report;
use App\Role;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list reports.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function list(User $user)
    {
        return $user->hasRole(Role::getAdmin());
    }

    /**
     * Determine whether the user can view the report.
     *
     * @param  \App\User  $user
     * @param  Report  $report
     * @return mixed
     */
    public function view(User $user, Report $report)
    {
        return $user->hasRole(Role::getAdmin());
    }

    /**
     * Determine whether the user can create reports.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the report.
     *
     * @param  \App\User  $user
     * @param  Report  $report
     * @return mixed
     */
    public function update(User $user, Report $report)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the report.
     *
     * @param  \App\User  $user
     * @param  Report  $report
     * @return mixed
     */
    public function delete(User $user, Report $report)
    {
        return false;//or can admins do it?
    }
}

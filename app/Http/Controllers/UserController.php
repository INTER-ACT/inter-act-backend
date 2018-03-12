<?php

namespace App\Http\Controllers;

use App\Domain\Manipulators\UserManipulator;
use App\Domain\PageGetRequest;
use App\Domain\SortablePageGetRequest;
use App\Domain\UserRepository;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\PendingUserCreatedResponse;
use App\Http\Resources\UserResources\UserCollection;
use App\Role;
use Auth;

class UserController extends Controller
{
    /** @var UserRepository */
    protected $repository;

    /**
     * DiscussionController constructor.
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param PageGetRequest $getRequest
     * @return UserCollection
     * @throws NotPermittedException
     */
    public function index(PageGetRequest $getRequest)
    {
        if(!$this->isAdmin())
            throw new NotPermittedException('Only admins can get a list of all Users.');

        return $this->repository->getAll($getRequest);
    }

    /**
     * @param CreateUserRequest $request
     * @return PendingUserCreatedResponse
     * @throws InternalServerError
     */
    public function store(CreateUserRequest $request)
    {
        $request->validate();
        UserManipulator::create($request->getData());
        return new PendingUserCreatedResponse();
    }

    /**
     * @param string $verification_token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function verifyUser(string $verification_token)
    {
        $user = UserManipulator::verifyUser($verification_token);
        return redirect(config('app.home_url'));
    }

    public function show(int $id)
    {
        return $this->repository->getByIdShort($id);
    }

    public function showDetails(int $id)
    {
        if($this->isUser($id))
            return $this->repository->getById($id);
        else
            throw new NotPermittedException('Only the User himself is allowed to see his details.');
    }

    public function update(int $id, UpdateUserRequest $request)
    {
        if(!$this->isUser($id))
            throw new NotPermittedException('You are not permitted to do this, accounts can only be altered by their owners.');

        $request->validate();

        UserManipulator::update($id, $request->getData());

        return response('', 204);
    }

    /*public function updatePassword(string $verification_token)
    {
        $user = UserManipulator::verifyPasswordUpdate($verification_token);
        return redirect(config('app.home_url'));
    }*/

    public function destroy(int $id)
    {
        if(!($this->isUser($id) || $this->isAdmin()))
            throw new NotPermittedException('You are not permitted to do this, accounts can only be deleted by their owners and admins.');

        UserManipulator::delete($id);

        return response('', 204);
    }

    // TODO update in documentation : SortablePageGetRequest as additional param in list operations

    /**
     * Lists all Discussions the user has created
     *
     * @param int $id
     * @param SortablePageGetRequest $request
     * @return \App\Http\Resources\DiscussionResources\DiscussionCollection
     */
    public function listDiscussions(int $id, SortablePageGetRequest $request)
    {
        return $this->repository->getDiscussions($id, $request);
    }

    public function listAmendments(int $id, SortablePageGetRequest $request)
    {
        return $this->repository->getAmendments($id, $request);
    }

    public function listSubAmendments(int $id, SortablePageGetRequest $request)
    {
        return $this->repository->getSubAmendments($id, $request);
    }

    public function listComments(int $id, SortablePageGetRequest $request)
    {
        return $this->repository->getComments($id, $request);
    }

    public function listReports(int $id, SortablePageGetRequest $request)
    {
        // TODO update this in documentation: this funciton only makes sense when /users
        // has a subresource /reports
        // only the user him/herself and the admin are allowed to view this page

        if(!($this->isUser($id) || $this->isAdmin()))
            throw new NotPermittedException('You are not permitted to do this, only admins and owners are allowed to view users reports.');

        return $this->repository->getReports($id, $request);
    }

    public function showStatistics(int $id)
    {
        return $this->repository->getStatistics($id);
    }

    public function dashboard()
    {
        // TODO figure out what should be displayed on the dashboard
    }

    public function updateRole(int $id, UpdateUserRoleRequest $request)
    {
        if( $this->isAdmin()){
            UserManipulator::updateRole($id, $request);

            return response('', 204);
        }
        throw new NotPermittedException('Only Admins are allowed to change User Roles.');
    }

    // TODO move authorization to its own class
    /**
     * Returns true, if a user is logged in and the id belongs to the logged in user
     *
     * @param int $userId
     * @return bool
     */
    protected function isUser(int $userId)
    {
        if(Auth::check()) {
            $authenticatedUser = Auth::user();
            return $authenticatedUser->id == $userId;
        }
        return false;
    }

    /**
     * Returns true, if the logged in user is an admin
     *
     * @return bool
     */
    protected function isAdmin()
    {
        $user = Auth::user();
        return $user->hasRole(Role::getAdmin());
    }

}

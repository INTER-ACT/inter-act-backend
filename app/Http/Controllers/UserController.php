<?php

namespace App\Http\Controllers;

use App\Domain\Manipulators\UserManipulator;
use App\Domain\PageGetRequest;
use App\Domain\User\UserRepository;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\SuccessfulCreationResource;
use App\Http\Resources\UserResources\UserCollection;
use App\Permission;
use App\User;
use Auth;
use Illuminate\Http\Request;

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
     * @return UserCollection
     */
    public function index()
    {
        $getRequest = new PageGetRequest();

        return $this->repository->getAll($getRequest);
    }

    /**
     * @param CreateUserRequest $request
     * @return SuccessfulCreationResource
     * @throws InternalServerError
     */
    public function store(CreateUserRequest $request)
    {
        // TODO send verification email

        $request->validate();

        $user = UserManipulator::create($request->getData());

        return new SuccessfulCreationResource($user);
    }

    public function show(int $id)
    {
        if($this->isUser($id))
            return $this->repository->getById($id);
        else
            return $this->repository->getByIdShort($id);
    }

    public function update(int $id, UpdateUserRequest $request)
    {
        if(!$this->isUser($id))
            throw new NotPermittedException('You are not permitted to do this, accounts can only be altered by their owners.');

        $request->validate();

        UserManipulator::update($id, $request->getData());

        return response('', 204);
    }

    // TODO patch method  -  what is the difference to update??

    public function destroy(int $id)
    {
        if(!($this->isUser($id) || $this->isAdmin()))
            throw new NotPermittedException('You are not permitted to do this, accounts can only be deleted by their owners and admins.');

        UserManipulator::delete($id);

        return response('', 204);
    }

    // TODO update in documentation : PageGetRequest as additional param in list operations

    /**
     * Lists all Discussions the user has created
     *
     * @param int $id
     * @param PageGetRequest $request
     * @return \App\Http\Resources\DiscussionResources\DiscussionCollection
     */
    public function listDiscussions(int $id, PageGetRequest $request)
    {
        return $this->repository->getDiscussions($id, $request);
    }

    public function listAmendments(int $id, PageGetRequest $request)
    {
        return $this->repository->getAmendments($id, $request);
    }

    public function listSubAmendments(int $id, PageGetRequest $request)
    {
        return $this->repository->getSubAmendments($id, $request);
    }

    public function listComments(int $id, PageGetRequest $request)
    {
        return $this->repository->getComments($id, $request);
    }

    public function listReports(int $id, PageGetRequest $request)
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

    // TODO move authorization to its own class
    /**
     * Returns true, if the id belongs to the logged in user
     *
     * @param int $userId
     * @return bool
     */
    protected function isUser(int $userId)
    {
        $authenticatedUser = Auth::user();
        return $authenticatedUser->id == $userId;
    }

    /**
     * Returns true, if the logged in user is an admin
     *
     * @return bool
     */
    protected function isAdmin()
    {
        $user = Auth::user();
        return $user->can(Permission::getAdministrate());
    }

}

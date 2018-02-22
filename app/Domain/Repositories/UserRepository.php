<?php

namespace App\Domain;


use App\Amendments\SubAmendment;
use App\Discussions\Discussion;
use App\Domain\CustomPaginationTrait;
use App\Domain\IRestRepository;
use App\Domain\PageGetRequest;
use App\Domain\SortablePageGetRequest;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;
use App\Http\Resources\UserResources\ShortUserResource;
use App\Http\Resources\UserResources\UserCollection;
use App\Http\Resources\UserResources\UserResource;
use App\Http\Resources\UserResources\UserStatisticsResource;
use App\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class UserRepository implements IRestRepository
{
    use CustomPaginationTrait;

    /**
     * @return string
     */
    public function getRestResourcePath()
    {
        return "/users";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return 'users';
    }

    /**
     * @return string
     */
    public function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }

    /**
     * @param PageGetRequest $pageRequest
     * @return UserCollection
     */
    public function getAll(PageGetRequest $pageRequest)
    {
        $users = new UserCollection($this->paginate(User::select()->orderBy('username', 'desc')->get(), $pageRequest->perPage, $pageRequest->pageNumber, 'users'));

        return $users;
    }

    /**
     * Returns a Full representation of the User,
     * Only the User himself is allowed to see this representation
     *
     * @param int $id
     * @return UserResource
     */
    public function getById(int $id)
    {
        $user = self::getByIdOrThrowError($id);

        return new UserResource($user);
    }

    /**
     * Returns a short representation of the User, which everyone is allowed to see
     *
     * @param int $id
     * @return ShortUserResource
     */
    public function getByIdShort(int $id)
    {
        $user = self::getByIdOrThrowError($id);

        return new ShortUserResource($user);
    }

    /**
     * Returns a Collection of all discussions that the user created
     *
     * @param int $id UserId
     * @param SortablePageGetRequest $pageRequest
     * @return DiscussionCollection
     */
    public function getDiscussions(int $id, SortablePageGetRequest $pageRequest)
    {
        //$discussions = Discussion::all()->where('user_id', '=', $id)->paginate($pageRequest->perPage,
        //    ['*'],'discussions', $pageRequest->pageNumber);

        $discussions = User::find($id)->discussions()->paginate($pageRequest->perPage, ['*'],'discussions', $pageRequest->pageNumber);


        return new DiscussionCollection($discussions);
    }

    /**
     * @param int $id
     * @param \App\Domain\SortablePageGetRequest $request
     * @return AmendmentCollection
     */
    public function getAmendments(int $id, SortablePageGetRequest $request)
    {
        $amendments = User::find($id)->amendments()->paginate($request->perPage, ['*'], 'amendments', $request->pageNumber);

        return new AmendmentCollection($amendments);
    }

    /**
     * @param int $id
     * @param \App\Domain\SortablePageGetRequest $request
     * @return SubAmendmentCollection
     */
    public function getSubAmendments(int $id, SortablePageGetRequest $request)
    {
        $subAmendments = User::find($id)->sub_amendments()->paginate($request->perPage, ['*'], 'sub_amendments', $request->pageNumber);

        return new SubAmendmentCollection($subAmendments);
    }

    /**
     * @param int $id
     * @param \App\Domain\SortablePageGetRequest $request
     * @return CommentCollection
     */
    public function getComments(int $id, SortablePageGetRequest $request)
    {
        $comments = User::find($id)->comments()->paginate($request->perPage, ['*'], 'comments', $request->pageNumber);

        return new CommentCollection($comments);
    }

    /**
     * @param int $id
     * @param \App\Domain\SortablePageGetRequest $request
     * @return DiscussionCollection
     */
    public function getRelevantDiscussions(int $id, SortablePageGetRequest $request)
    {
        $foo = new ActionRepository();
        return $foo->getRelevantDiscussions($id);
    }

    /**
     * @param int $id
     * @param \App\Domain\SortablePageGetRequest $request
     * @return ReportCollection
     */
    public function getReports(int $id, SortablePageGetRequest $request)
    {
        $reports = User::find($id)->reports()->paginate($request->perPage, ['*'], 'reports', $request->pageNumber);

        return new ReportCollection($reports);
    }

    /**
     * @param int $id
     * @return UserStatisticsResource
     */
    public function getStatistics(int $id)
    {
        $user = User::find($id);

        return new UserStatisticsResource($user);
    }

    /**
     * @param int $id
     * @return User
     * @throws NotFoundException
     */
    public static function getByIdOrThrowError(int $id)
    {
        $user = User::find($id);
        if($user === Null)
            throw new NotFoundException('No user was found with id ' . $id);

        return $user;
    }

}
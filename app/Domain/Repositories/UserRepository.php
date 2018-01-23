<?php

namespace App\Domain\User;


use App\Amendments\SubAmendment;
use App\Discussions\Discussion;
use App\Domain\IRestRepository;
use App\Domain\PageGetRequest;
use App\Domain\PageRequest;
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
     * @return LengthAwarePaginator
     */
    public function getAll(PageGetRequest $pageRequest)
    {
        $users = new UserCollection(User::all());

        return $pageRequest->getPaginatedCollection($users);
    }

    /**
     * @param int $id
     * @return mixed either UserResource or ShortUserResource depending on whether the logged in user is requesting his own information
     */
    public function getById(int $id)
    {
        $user = User::find($id);

        if($user === Null)
            throw new Exception(); // TODO get actual exception

        if(Auth::check()){
            $loggedInUser = Auth::user();

            if($loggedInUser->id == $user->id)
                return new UserResource($user);
        }
        else{
            return new ShortUserResource($user);
        }
    }

    /**
     * Returns a Collection of all discussions that the user created
     *
     * @param int $id UserId
     * @param PageGetRequest $pageRequest
     * @return DiscussionCollection
     */
    public function getDiscussions(int $id, PageGetRequest $pageRequest)
    {
        //$discussions = Discussion::all()->where('user_id', '=', $id)->paginate($pageRequest->perPage,
        //    ['*'],'discussions', $pageRequest->pageNumber);

        $discussions = User::find($id)->discussions()->paginate($pageRequest->perPage, ['*'],'discussions', $pageRequest->pageNumber);


        return new DiscussionCollection($discussions);
    }

    public function getAmendments(int $id, PageGetRequest $request)
    {
        $amendments = User::find($id)->amendments()->paginate($request->perPage, ['*'], 'amendments', $request->pageNumber);

        return new AmendmentCollection($amendments);
    }

    public function getSubAmendments(int $id, PageGetRequest $request)
    {
        $subAmendments = User::find($id)->sub_amendments()->paginate($request->perPage, ['*'], 'sub_amendments', $request->pageNumber);

        return new SubAmendmentCollection($subAmendments);
    }

    public function getComments(int $id, PageGetRequest $request)
    {
        $comments = User::find($id)->comments()->paginate($request->perPage, ['*'], 'comments', $request->pageNumber);

        return new CommentCollection($comments);
    }

    public function getRelevantDiscussions(int $id, PageGetRequest $request)
    {
        // TODO check whether this exists in api doc; maybe add it to discussions instead : Popularity or relevance
    }

    public function getReports(int $id, PageGetRequest $request)
    {
        $reports = User::find($id)->reports()->paginate($request->perPage, ['*'], 'reports', $request->pageNumber);

        return new ReportCollection($reports);
    }

    public function getStatistics(int $id)
    {
        $user = User::find($id);

        return new UserStatisticsResource($user);
    }

}
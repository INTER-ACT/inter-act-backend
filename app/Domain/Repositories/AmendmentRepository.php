<?php

namespace App\Domain;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Discussions\Discussion;
use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\AmendmentResources\AmendmentResource;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\RatingResources\MultiAspectRatingResource;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;
use App\Http\Resources\UserResources\UserResource;
use App\User;
use Illuminate\Http\Request;

class AmendmentRepository implements IRestRepository
{
    use CustomPaginationTrait;

    const SORT_BY_POPULARITY = 'popularity';
    const SORT_BY_CHRONOLOGICAL = 'chronological';

    const SORT_DESC = 'desc';
    const SORT_ASC = 'asc';

    /**
     * @return string
     */
    function getRestResourcePath()
    {
        return "/amendments";
    }

    /**
     * @return string
     */
    function getRestResourceName()
    {
        return "amendments";
    }

    /**
     * @return string
     */
    function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }

    /**
     * @param int $user_id
     * @param PageGetRequest $request
     * @return AmendmentCollection
     * @throws CannotResolveDependenciesException
     */
    public function getAllByUser(int $user_id, PageGetRequest $request)
    {
        if (!User::find($user_id)->exists())
            throw new CannotResolveDependenciesException("The User $user_id does not exist.");

        $amendments = Amendment::all()->where('user_id', '=', $user_id);

        return new AmendmentCollection($this->paginate($amendments));
    }

    /**
     * @param int $discussion_id
     * @param PageGetRequest $request
     * @param int $sortDirection
     * @param $sortBy
     * @return AmendmentCollection
     * @throws CannotResolveDependenciesException
     */
    public function getAll(int $discussion_id, PageGetRequest $request, $sortDirection = SORT_DESC, $sortBy = self::SORT_BY_POPULARITY)
    {
        $discussion = Discussion::find($discussion_id);
        if ($discussion === Null)
            throw new CannotResolveDependenciesException("The Discussion $discussion_id does not exist!");

        if ($sortBy == 'chronological') {
            $amendments = Amendment::where('discussion_id', '=', $discussion_id)
                ->orderBy('created_at', $sortDirection)
                ->get();
        } else {
            $amendments = Amendment::where('discussion_id', '=', $discussion_id);

            $amendment_keys = [];
            foreach ($amendments as $amendment)
                $amendment_keys[$amendment->getActivity()] = $amendment;

            if ($sortDirection == SORT_DESC)
                krsort($amendment_keys);
            else if ($sortDirection == SORT_ASC)
                ksort($amendment_keys);

            $amendments = array_values($amendment_keys);
        }

        return new AmendmentCollection($amendments);
    }

    /**
     * @param int $id
     * @return AmendmentResource
     */
    public function getById(int $id)
    {
        return new AmendmentResource(self::getByIdOrThrowError($id));
    }

    /**
     * Returns the SubAmendments of an Amendments,
     *  with pagination,
     *  of a specific state (pending, accepted, rejected, all)
     *  and sorted
     *
     * @param int $id
     * @param PageGetRequest $request
     * @param string $sortDirection
     * @param string $sortedBY
     * @param string $state
     * @return SubAmendmentCollection
     */
    public function getSubAmendments(int $id, PageGetRequest $request, $sortDirection=self::SORT_DESC,
                                     $sortedBY=self::SORT_BY_POPULARITY, $state=SubAmendment::PENDING_STATUS)
    {
        $amendment = self::getByIdOrThrowError($id);

        if($state != 'all')
            $subamendments = $amendment->sub_amendments()->where('status', '=', $state)->get();
        else
            $subamendments = $amendment->sub_amendments;

        $paginatedSubAmendments = $this->paginate($subamendments, $request->perPage, $request->pageNumber);

        return new SubAmendmentCollection($paginatedSubAmendments);
    }


    public function getChanges(int $id, PageGetRequest $request)
    {
        // TODO implement getChanges
    }

    /**
     * Returns all comments from the Amendment,
     *  sorted chronologically (most recent first),
     *  paginated
     *
     * @param int $id from Amendment
     * @param PageGetRequest $request
     * @return CommentCollection
     */
    public function getComments(int $id, PageGetRequest $request)
    {
        $amendment = self::getByIdOrThrowError($id);
        $comments = $this->paginate($amendment->comments()->orderBy('created_at', 'desc')->get(), $request->perPage, $request->pageNumber);

        return new CommentCollection($comments);
    }

    /**
     * Returns the MultiAspect Rating for the Amendment,
     * including User details, if the User is provided
     *
     * @param int $id
     * @param User|Null $user
     * @return MultiAspectRatingResource
     */
    public function getRating(int $id, User $user = Null)
    {
        $amendment = self::getByIdOrThrowError($id);

        $amendmentResource = new MultiAspectRatingResource($amendment);

        if ($user === Null)
            $amendmentResource->user = $user;

        return $amendmentResource;
    }

    /**
     * Returns the Reports issued for the amendment
     *
     * @param int $id
     * @return ReportCollection
     */
    public function getReports(int $id)
    {
        $amendment = self::getByIdOrThrowError($id);

        $reports = $amendment->reports();

        return new ReportCollection($reports);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundException
     */
    public static function getByIdOrThrowError(int $id)
    {
        $amendment = Amendment::find($id);

        if ($amendment === Null)
            throw new NotFoundException("No Amendment found with id $id");

        return $amendment;
    }
}
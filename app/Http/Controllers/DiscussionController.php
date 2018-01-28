<?php

namespace App\Http\Controllers;

use App\Domain\DiscussionRepository;
use App\Domain\LawRepository;
use App\Domain\LawResourceShort;
use App\Domain\Manipulators\DiscussionManipulator;
use App\Domain\OgdRisApiBridge;
use App\Domain\PageGetRequest;
use App\Domain\PageRequest;
use App\Domain\SortablePageGetRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Http\Requests\CreateAmendmentRequest;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\CreateDiscussionRequest;
use App\Http\Requests\CreateMultiAspectRatingRequest;
use App\Http\Requests\DeleteDiscussionRequest;
use App\Http\Requests\ListLawTextsRequest;
use App\Http\Requests\ShowLawTextRequest;
use App\Http\Requests\UpdateDiscussionRequest;
use App\Http\Requests\ViewDiscussionRequest;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CreatedResponseResource;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\DiscussionResources\DiscussionResource;
use App\Http\Resources\DiscussionResources\DiscussionStatisticsResource;
use App\Http\Resources\LawCollection;
use App\Http\Resources\LawResource;
use App\Http\Resources\MultiAspectRatingResource;
use App\Http\Resources\NoContentResource;
use App\Http\Resources\SuccessfulCreationResource;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Log;

class DiscussionController extends Controller
{
    /** @var DiscussionRepository */
    protected $repository;

    /**
     * DiscussionController constructor.
     * @param DiscussionRepository $repository
     */
    public function __construct(DiscussionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function test(string $id) : string
    {
        return OgdRisApiBridge::test($id);
    }

    /**
     * @param CreateDiscussionRequest $request
     * @return SuccessfulCreationResource
     */
    public function store(CreateDiscussionRequest $request) : SuccessfulCreationResource
    {
        return DiscussionManipulator::create(\Auth::user(), $request->all());
    }

    /**
     * @param Request $request
     * @return DiscussionCollection
     */
    public function index(Request $request) : DiscussionCollection
    {
        $tag_id = Input::get('tag_id', null);
        return $this->repository->getAll(new PageGetRequest($request), $request->sorted_by, $request->sort_direction, $tag_id);
    }

    /**
     * @param ViewDiscussionRequest $request
     * @param int $id
     * @return DiscussionResource
     */
    public function show(ViewDiscussionRequest $request, int $id) : DiscussionResource
    {
        return $this->repository->getById($id);
    }

    /**
     * @param UpdateDiscussionRequest $request
     * @param int $id
     * @return NoContentResource
     */
    public function update(UpdateDiscussionRequest $request, int $id) : NoContentResource
    {
        DiscussionManipulator::update($id, $request->all());
        return new NoContentResource($request);
    }

    /**
     * @param DeleteDiscussionRequest $request
     * @param int $id
     * @return NoContentResource
     */
    public function destroy(DeleteDiscussionRequest $request, int $id) : NoContentResource
    {
        DiscussionManipulator::delete($id);
        return new NoContentResource($request);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return MultiAspectRatingResource
     */
    public function getRating(Request $request, int $id) : MultiAspectRatingResource //TODO: care that id is passed or just leave InternalServerError?
    {
        return $this->repository->getRating($id);
    }

    /**
     * @param CreateMultiAspectRatingRequest $request
     * @param int $id
     * @return \App\Http\Resources\SuccessfulCreationResourceNoId
     */
    public function createRating(CreateMultiAspectRatingRequest $request, int $id)
    {
        return DiscussionManipulator::createRating($id, \Auth::id(), $request->all());
    }

    /**
     * @param Request $request
     * @param int $id
     * @return AmendmentCollection
     */
    public function listAmendments(Request $request, int $id) : AmendmentCollection
    {
        return $this->repository->getAmendments($id, $request->sorted_by, $request->sort_direction, new PageGetRequest($request));
    }

    /**
     * @param int $id
     * @param CreateAmendmentRequest $request
     * @return SuccessfulCreationResource
     */
    public function createAmendment(CreateAmendmentRequest $request, int $id) : SuccessfulCreationResource    //TODO: change to CreateAmendmentRequest
    {
        return DiscussionManipulator::createAmendment($id, $request->all(), \Auth::id());
    }

    /**
     * @param Request $request
     * @param int $id
     * @return CommentCollection
     */
    public function listComments(Request $request, int $id) : CommentCollection
    {
        $perPage = Input::get('count', 10);
        $pageNumber = Input::get('start', 1);
        return $this->repository->getComments($id, new PageGetRequest($request));
    }

    /**
     * @param int $id
     * @param CreateCommentRequest $request
     * @return SuccessfulCreationResource
     */
    public function createComment(CreateCommentRequest $request, int $id) : SuccessfulCreationResource    //TODO: change to CreateCommentRequest
    {
        return DiscussionManipulator::createComment($id, $request->all(), \Auth::id());
    }

    /**
     * @param int $id
     * @return DiscussionStatisticsResource
     */
    public function showStatistics(int $id) : DiscussionStatisticsResource  //TODO: remove if not needed
    {
        return $this->repository->getStatistics($id);
    }

    /**
     * @param ListLawTextsRequest $request
     * @param LawRepository $lawRepository
     * @return LawCollection
     */
    public function listLawTexts(ListLawTextsRequest $request, LawRepository $lawRepository) : LawCollection
    {
        return $lawRepository->getAll(new PageGetRequest($request));
    }

    /**
     * @param ShowLawTextRequest $request
     * @param LawRepository $lawRepository
     * @param string $id
     * @return LawResource
     */
    public function showLawText(ShowLawTextRequest $request, LawRepository $lawRepository, string $id) : LawResource
    {
        return $lawRepository->getOne($id);
    }
}

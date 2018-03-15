<?php

namespace App\Http\Controllers;

use App\Amendments\SubAmendment;
use App\Domain\AmendmentRepository;
use App\Domain\Manipulators\AmendmentManipulator;
use App\Domain\PageGetRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Http\Requests\CreateAmendmentCommentRequest;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\CreateReportRequest;
use App\Http\Requests\CreateSubAmendmentRequest;
use App\Http\Requests\GetAmendmentsRequest;
use App\Http\Requests\GetSubAmendmentsRequest;
use App\Http\Requests\UpdateAmendmentRatingRequest;
use App\Http\Resources\NoContentResource;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\SuccessfulCreationResource;
use App\Role;
use Auth;
use Illuminate\Http\Request;

class AmendmentController extends Controller
{
    /** @var AmendmentRepository */
    protected $repository;

    /**
     * AmendmentController constructor.
     * @param AmendmentRepository $repository
     */
    public function __construct(AmendmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $discussion_id
     * @param GetAmendmentsRequest $request
     * @return \App\Http\Resources\AmendmentResources\AmendmentCollection
     */
    public function index(int $discussion_id, GetAmendmentsRequest $request)
    {
        return $this->repository->getAll($discussion_id, $request, $request->sortDirection, $request->sortedBy);
    }

    /**
     * @param int $discussion_id
     * @param int $id
     * @return \App\Http\Resources\AmendmentResources\AmendmentResource
     */
    public function show(int $discussion_id, int $id)
    {
        return $this->repository->getById($id);
    }

    /**
     * @param int $id
     * @return \App\Http\Resources\AmendmentResources\AmendmentResource
     */
    public function showShort(int $id)
    {
        return $this->show(-1, $id);
    }

    /**
     * Deletes the Amendment
     *
     * @param int $id
     * @param Request $request
     * @return NoContentResource
     * @throws NotPermittedException
     */
    public function destroy(int $id, Request $request)
    {
        // TODO Amendments shouldn't be deleted, just archived

        if(!Auth::user()->hasRole(Role::getAdmin()))
            throw new NotPermittedException('Amendments can only be deleted by Admins');

        AmendmentManipulator::delete($id);

        return new NoContentResource();
    }

    /**
     * @param int $id
     * @param GetSubAmendmentsRequest $request
     * @return \App\Http\Resources\SubAmendmentResources\SubAmendmentCollection
     */
    public function listSubAmendments(int $id, GetSubAmendmentsRequest $request)
    {
        return $this->repository->getSubAmendments($id, $request, $request->sortDirection, $request->sortedBy,
                                                        $request->state);
    }

    /**
     * @param int $id
     * @param PageGetRequest $request
     */
    public function listChanges(int $id, PageGetRequest $request)
    {
        return $this->repository->getChanges($id, $request);
    }

    /**
     * @param int $id
     * @param CreateSubAmendmentRequest $request
     * @return SuccessfulCreationResource
     */
    public function createSubAmendment(int $id, CreateSubAmendmentRequest $request)
    {
        $user = Auth::user();
        $subamendment = AmendmentManipulator::createSubAmendment($id, $request, $user->id);

        return new SuccessfulCreationResource($subamendment);
    }

    /**
     * @param int $id
     * @param PageGetRequest $request
     * @return \App\Http\Resources\CommentResources\CommentCollection
     */
    public function listComments(int $id, PageGetRequest $request)
    {
        return $this->repository->getComments($id, $request);
    }

    /**
     * @param int $id
     * @param CreateAmendmentCommentRequest $request
     * @return SuccessfulCreationResource
     */
    public function createComment(int $id, CreateAmendmentCommentRequest $request)
    {
        $user = Auth::user();

        $comment = AmendmentManipulator::createComment($id, $request, $user->id);

        return new SuccessfulCreationResource($comment);
    }

    /**
     * @param int $id
     * @return \App\Http\Resources\RatingResources\MultiAspectRatingResource
     */
    public function showRating(int $id)
    {
        return $this->repository->getRating($id);
    }

    /***
     * @param int $id
     * @param UpdateAmendmentRatingRequest $request
     * @return NoContentResource
     */
    public function updateRating(int $id, UpdateAmendmentRatingRequest $request)
    {
        $user = Auth::user();

        AmendmentManipulator::updateRating($id, $request, $user->id);

        return new NoContentResource();
    }

}

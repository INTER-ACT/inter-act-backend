<?php

namespace App\Http\Controllers;

use App\Domain\Manipulators\SubAmendmentManipulator;
use App\Domain\PageGetRequest;
use App\Domain\SubAmendmentRepository;
use App\Http\Requests\CreateAmendmentCommentRequest;
use App\Http\Requests\CreateMultiAspectRatingRequest;
use App\Http\Requests\DeleteSubAmendmentRequest;
use App\Http\Requests\UpdateSubAmendmentRequest;
use App\Http\Resources\MultiAspectRatingResource;
use App\Http\Resources\NoContentResource;
use App\Http\Resources\SuccessfulCreationResource;
use Auth;

class SubAmendmentController extends Controller
{
    /** @var SubAmendmentRepository */
    protected $repository;

    /**
     * SubAmendmentController constructor.
     * @param SubAmendmentRepository $repository
     */
    public function __construct(SubAmendmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Fetches the SubAmendment and returns its resource
     *
     * @param int $id
     * @return \App\Http\Resources\SubAmendmentResources\SubAmendmentResource
     */
    public function show(int $id)
    {
        // TODO implement restriction to returned fields
        return $this->repository->getById($id);
    }

    /**
     * @param int $id
     * @return \App\Http\Resources\SubAmendmentResources\SubAmendmentResource
     */
    public function showShort(int $id)
    {
        return $this->show($id);
    }

    /**
     * Changes the status of the SubAmendment (accept / reject)
     *
     * @param int $id
     * @param UpdateSubAmendmentRequest $request
     * @return NoContentResource
     */
    public function patch(int $id, UpdateSubAmendmentRequest $request)
    {
        $subamendment = $this->repository::getByIdOrThrowError($id);
        $amendment_id = $subamendment->amendment_id;

        $request->validate();
        $request->authorize($amendment_id);

        if($request->getData()['accept'])
            SubAmendmentManipulator::accept($id);
        else
            SubAmendmentManipulator::reject($id, $request->getData()['explanation']);

        return new NoContentResource();
    }


    /**
     * Deletes the SubAmendment
     *
     * @param int $id
     * @param DeleteSubAmendmentRequest $request
     * @return NoContentResource
     */
    public function destroy(int $id, DeleteSubAmendmentRequest $request)
    {
        SubAmendmentManipulator::delete($id);
        return new NoContentResource();
    }

    /**
     * Updates the Subamendment's Multi Aspect Rating
     *
     * @param int $id
     * @param CreateMultiAspectRatingRequest $request
     * @return NoContentResource
     */
    public function updateRating(int $id, CreateMultiAspectRatingRequest $request)
    {
        $user = Auth::user();
        SubAmendmentManipulator::createRating($id, $user->id, $request->all());

        return new NoContentResource();
    }

    /**
     * Fetch the overall rating of the subamendment and the user specific rating, if a user is logged in
     *
     * @param $id
     * @return MultiAspectRatingResource
     */
    public function showRating($id)
    {
        $subamendment = SubAmendmentRepository::getByIdOrThrowError($id);

        return new MultiAspectRatingResource($subamendment);
    }

    /**
     * Lists all Comments of the Subamendment
     * with Pagination
     *
     * @param int $id
     * @param PageGetRequest $request
     * @return \App\Http\Resources\CommentResources\CommentCollection
     */
    public function listComments(int $id, PageGetRequest $request)
    {
        return $this->repository->getComments($id, $request);
    }

    /**
     * Creates a comment for a subamendment
     *
     * @param int $id
     * @param CreateAmendmentCommentRequest $request
     * @return SuccessfulCreationResource
     */
    public function createComment(int $id, CreateAmendmentCommentRequest $request)
    {
        $user = Auth::user();
        $comment = SubAmendmentManipulator::createComment($id, $request->getData(), $user->id);

        return new SuccessfulCreationResource($comment);
    }

    public function showChange(int $id)
    {
        // TODO still required?
    }

    public function showRejection(int $id)
    {
        // TODO required?
    }
}

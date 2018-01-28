<?php

namespace App\Domain\Manipulators;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Domain\AmendmentRepository;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Requests\CreateAmendmentCommentRequest;
use App\Http\Requests\CreateReportRequest;
use App\Http\Requests\CreateSubAmendmentRequest;
use App\Http\Requests\UpdateAmendmentRatingRequest;
use App\Reports\Report;

class AmendmentManipulator
{
    /**
     * @param int $id
     * @throws InternalServerError
     * @throws NotFoundException
     */
    public function delete(int $id)
    {
        $amendment = Amendment::find($id);
        if($amendment === Null)
            throw new NotFoundException("The Amendment $id could not be found.");

        if(!$amendment->delete())
            throw new InternalServerError("The Amendment $id could not be deleted.");
    }

    /**
     * @param int $id
     * @param CreateSubAmendmentRequest $request
     * @param int $user_id
     * @throws InternalServerError
     */
    public function createSubAmendment(int $id, CreateSubAmendmentRequest $request, int $user_id)
    {
        $subAmendment = new SubAmendment();

        $subAmendment->amendment_id = $id;
        $subAmendment->fill($request->getData());

        if(!$subAmendment->save())
            throw new InternalServerError("Subamendment could not be created.");
    }

    /**
     * @param int $id
     * @param CreateAmendmentCommentRequest $request
     * @param int $user_id
     * @throws InternalServerError
     */
    public function createComment(int $id, CreateAmendmentCommentRequest $request, int $user_id)
    {
        $amendment = AmendmentRepository::getByIdOrThrowError($id);

        $comment = new Comment();
        $comment->fill($request->getData());

        if(!$amendment->comments()->save($comment))
            throw new InternalServerError('The Comment could not be created.');
    }

    /**
     * @param int $id
     * @param CreateReportRequest $request
     * @param int $user_id
     * @throws InternalServerError
     */
    public function createReport(int $id, CreateReportRequest $request, int $user_id)
    {
        $amendment = AmendmentRepository::getByIdOrThrowError($id);

        $report = new Report();
        $report->fill($request->getData());

        if(!$amendment->reports()->save($report))
            throw new InternalServerError('The Report could not be created.');
    }

    /**
     * @param int $id
     * @param UpdateAmendmentRatingRequest $request
     * @param int $user_id
     */
    public function updateRating(int $id, UpdateAmendmentRatingRequest $request, int $user_id)
    {
        $amendment = AmendmentRepository::getByIdOrThrowError($id);
        // TODO implement Rating update
    }
}
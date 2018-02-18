<?php

namespace App\Domain;


use App\Amendments\SubAmendment;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\MultiAspectRatingResource;
use App\Http\Resources\SubAmendmentResources\SubAmendmentResource;

class SubAmendmentRepository implements IRestRepository
{
    use CustomPaginationTrait;

    /**
     * @return string
     */
    function getRestResourcePath()
    {
        return '/subamendments';
    }

    /**
     * @return string
     */
    function getRestResourceName()
    {
        return 'subamendments';
    }

    /**
     * @return string
     */
    function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }

    /**
     * @param int $id
     * @return SubAmendmentResource
     * @throws NotFoundException
     */
    public function getById(int $id)
    {
        return new SubAmendmentResource(self::getByIdOrThrowError($id));
    }

    public function getChangeById(int $id)
    {
    }

    public function getRejectionById(int $id)
    {
        // TODO implement Rejection Resource, what is this even supposed to be?
    }

    /**
     * Returns paginated comments of the subamendment
     *
     * @param int $id
     * @param PageGetRequest $request
     * @return CommentCollection
     */
    public function getComments(int $id, PageGetRequest $request)
    {
        $subamendment = self::getByIdOrThrowError($id);

        return new CommentCollection($this->paginate($subamendment->comments, $request->perPage, $request->pageNumber));
    }

    /**
     * @param int $id
     * @return MultiAspectRatingResource
     */
    public function getRating(int $id)
    {
        $subamendment = self::getByIdOrThrowError($id);

        return new MultiAspectRatingResource($subamendment);
    }

    /**
     * @param int $id
     */
    public function getReports(int $id)
    {
        // not required anymore
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundException
     */
    public static function getByIdOrThrowError(int $id)
    {
        $subamendment = SubAmendment::find($id);
        if($subamendment === Null)
            throw new NotFoundException("The subamendment $id could not be found");

        return $subamendment;
    }
}
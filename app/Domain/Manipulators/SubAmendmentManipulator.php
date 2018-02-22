<?php

namespace App\Domain\Manipulators;


use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Domain\SubAmendmentRepository;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\MultiAspectRating;

class SubAmendmentManipulator
{
    /**
     * Deletes a SubAmendment
     *
     * @param int $id
     * @throws InternalServerError
     */
    public static function delete(int $id)
    {
        $subamendment = SubAmendmentRepository::getByIdOrThrowError($id);

        if(!$subamendment->delete())
            throw new InternalServerError('The SubAmendment could not be deleted.');
    }

    /**
     * Creates a comment on a SubAmendment
     *
     * @param int $id
     * @param array $data  containing: comment_text, array tags (tag ids)
     * @param int $user_id
     * @return Comment
     * @throws InternalServerError
     */
    public static function createComment(int $id, array $data, int $user_id)
    {
        $subamendemnt = SubAmendmentRepository::getByIdOrThrowError($id);

        $comment = new Comment();

        $comment->user_id = $user_id;
        $comment->fill($data);

        if(!$subamendemnt->comments()->save($comment))
            throw new InternalServerError('The Comment could not be created.');

        $comment->tags()->attach($data['tags']);

        return $comment;
    }

    /**
     * Creates a new, or updates the existing MultiAspectRating
     *
     * @param int $id
     * @param int $user_id
     * @param array $data
     * @return MultiAspectRating
     * @throws InternalServerError
     */
    public static function createRating(int $id, int $user_id, array $data)
    {
        $subamendment = SubAmendmentRepository::getByIdOrThrowError($id);

        $rating = $subamendment->ratings()->where('user_id', '=', $user_id)->first();
        if($rating !== Null){
            if(!$rating->delete())
                throw new InternalServerError('The old rating could not be deleted.');
        }
        $rating = new MultiAspectRating();

        $rating->fill($data);
        $rating->user_id = $user_id;

        if(!$subamendment->ratings()->save($rating))
            throw new InternalServerError('Rating could not be created.');

        return $rating;
    }

    /**
     * @param int $id
     * @throws InternalServerError
     */
    public static function accept(int $id)
    {
        // TODO does the version NR have to be increased or something like that?

        $subamendment = SubAmendmentRepository::getByIdOrThrowError($id);
        $subamendment->handled_at = now();
        $subamendment->status = SubAmendment::ACCEPTED_STATUS;

        if(!$subamendment->save())
            throw new InternalServerError('The Subamendment could not be accepted.');
    }

    /**
     * @param int $id
     * @param string $explanation
     * @throws InternalServerError
     */
    public static function reject(int $id, string $explanation)
    {
        $subamendment = SubAmendmentRepository::getByIdOrThrowError($id);
        $subamendment->handled_at = now();
        $subamendment->status = SubAmendment::REJECTED_STATUS;
        $subamendment->handle_explanation = $explanation;

        if(!$subamendment->save())
            throw new InternalServerError('The Subamendment could not be rejected.');
    }

}
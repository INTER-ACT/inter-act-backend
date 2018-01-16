<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.01.18
 * Time: 07:51
 */

namespace App\Domain\Manipulators;


use App\Amendments\Amendment;
use App\Discussions\Discussion;
use App\Domain\DiscussionRepository;
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;

class DiscussionManipulator //TODO: request not validated here. remove this if done elsewhere
{
    /**
     * @param int $id
     * @param array $data
     * @throws ApiException
     */
    public static function update(int $id, array $data) : void
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        if(!$discussion->update($data))
            throw new ApiException(ApiExceptionMeta::getAInternalServerError(), 'Discussion with id ' . $id . ' could not be updated.');
    }

    /**
     * @param int $id
     * @throws ApiException
     */
    public static function delete(int $id) : void
    {
        $discussion = Discussion::find($id);
        if(!isset($discussion) or !($discussion instanceof Discussion))
            throw new ApiException(ApiExceptionMeta::getAInternalServerError(), 'Discussion with id ' . $id . ' does not exist.');
        if(!$discussion->delete())
            throw new ApiException(ApiExceptionMeta::getAInternalServerError(), 'Discussion with id ' . $id . ' could not be deleted.');
    }

    /**
     * @param int $id
     * @param array $data
     * @return int
     */
    public static function createAmendment(int $id, array $data) : int  //TODO: remove user_id in docs
    {
        $user = \Auth::user();
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        $amendment = new Amendment();
        $amendment->fill([$data]);
        $amendment->user_id = $user->id;
        $discussion->amendments()->save($amendment);
        return $amendment->id;
    }

    /**
     * @param int $id
     * @param array $data
     * @return int
     */
    public static function createComment(int $id, array $data) : int  //TODO: remove user_id in docs
    {
        $user = \Auth::user();
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        $comment = new Amendment();
        $comment->fill([$data]);
        $comment->user_id = $user->id;
        $discussion->comments()->save($comment);
        return $comment->id;
    }
}
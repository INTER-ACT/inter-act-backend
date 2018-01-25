<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.01.18
 * Time: 07:51
 */

namespace App\Domain\Manipulators;


use App\Amendments\Amendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Domain\DiscussionRepository;
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Http\Resources\SuccessfulCreationResource;
use App\User;
use Log;

class DiscussionManipulator //TODO: request not validated here. remove this if done elsewhere
{
    /**
     * @param User $user
     * @param array $data
     * @return SuccessfulCreationResource
     * @throws InternalServerError
     */
    public static function create(User $user, array $data) : SuccessfulCreationResource
    {
        $discussion = new Discussion();
        $discussion->fill($data);
        //Log::info(var_dump($data));
        $discussion->user_id = $user->id;
        if(!$discussion->save())
            throw new InternalServerError("Could not create a discussion with the given data.");
        $discussion->tags()->attach($data['tags']);
        return new SuccessfulCreationResource($discussion);
    }

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
        $tag_ids = $data['tags'];
        $discussion->tags()->attach($tag_ids);
    }

    /**
     * @param int $id
     * @return void
     * @throws InternalServerError
     */
    public static function delete(int $id) : void
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        $discussion->archived_at = now();
        if(!$discussion->save())
            throw new InternalServerError('Discussion with id ' . $id . 'could not be deleted.');
    }

    /**
     * @param int $id
     * @param array $data
     * @param int $user_id
     * @return SuccessfulCreationResource
     * @throws InternalServerError
     */
    public static function createAmendment(int $id, array $data, int $user_id) : SuccessfulCreationResource
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        $amendment = new Amendment();
        $amendment->fill($data);
        $amendment->user_id = $user_id;
        if(!$discussion->amendments()->save($amendment))
            throw new InternalServerError("Could not create an amendment with the given data.");
        $amendment->tags()->attach($data['tags']);
        return new SuccessfulCreationResource($amendment);
    }

    /**
     * @param int $id
     * @param array $data
     * @param int $user_id
     * @return SuccessfulCreationResource
     * @throws InternalServerError
     */
    public static function createComment(int $id, array $data, int $user_id) : SuccessfulCreationResource
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        $comment = new Comment();
        $comment->fill($data);
        $comment->user_id = $user_id;
        if(!$discussion->comments()->save($comment))
            throw new InternalServerError("Could not create a comment with the given data.");
        $comment->tags()->attach($data['tags']);
        return new SuccessfulCreationResource($comment);
    }
}
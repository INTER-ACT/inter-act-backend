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
use App\Http\Resources\SuccessfulCreationResourceNoId;
use App\MultiAspectRating;
use App\User;
use Log;

class DiscussionManipulator
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
        $discussion->user_id = $user->id;
        if(!$discussion->save())
            throw new InternalServerError("Could not create a Discussion with the given data.");
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
        if(isset($data['tags'])) {
            $tag_ids = $data['tags'];
            $discussion->tags()->sync($tag_ids);
        }
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
     * @param int $user_id
     * @param array $data
     * @return SuccessfulCreationResourceNoId
     * @throws InternalServerError
     */
    public static function createRating(int $id, int $user_id, array $data) : SuccessfulCreationResourceNoId
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($id);
        $rating = $discussion->ratings()->where('user_id', '=', $user_id)->first();
        if(!isset($rating))
            $rating = new MultiAspectRating();
        $rating->fill($data);
        $rating->user_id = $user_id;
        if(!$discussion->ratings()->save($rating))
            throw new InternalServerError('Rating could not be created.');
        return new SuccessfulCreationResourceNoId($discussion);
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
        $amendment->tags()->sync($data['tags']);
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
        $comment->tags()->sync($data['tags']);
        return new SuccessfulCreationResource($comment);
    }
}
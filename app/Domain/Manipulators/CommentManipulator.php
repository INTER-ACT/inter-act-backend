<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 16:26
 */

namespace App\Domain\Manipulators;


use App\Comments\Comment;
use App\Domain\CommentRepository;
use App\Domain\IlaiApi;
use App\Domain\UserRepository;
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Http\Resources\NoContentResource;
use App\Http\Resources\SuccessfulCreationResource;

class CommentManipulator
{
    const DELETED_COMMENT_CONTENT = '[entfernt]';

    /**
     * @param int $id
     * @param array $tags
     * @return NoContentResource
     * @throws InternalServerError
     */
    public static function update(int $id, array $tags) : NoContentResource
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->tags()->sync($tags);
        return new NoContentResource();
    }

    /**
     * @param int $id
     * @return void
     * @throws ApiException
     */
    public static function delete(int $id) : void
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->content = self::DELETED_COMMENT_CONTENT;
        if(!$comment->save())
            throw new InternalServerError('Comment with id ' . $id . ' could not be deleted.');
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
        $user = UserRepository::getByIdOrThrowError($user_id);
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $sub_comment = new Comment();
        $sub_comment->fill($data);
        $sub_comment->user_id = $user->id;
        $sub_comment->sentiment = IlaiApi::getSentimentForText($sub_comment->content);
        if(!$comment->comments()->save($sub_comment))
            throw new InternalServerError("Could not create a comment with the given data.");
        $sub_comment->tags()->sync($data['tags']);
        IlaiApi::sendTags($sub_comment->content, $sub_comment->tags->pluck('name')->all());
        return new SuccessfulCreationResource($sub_comment);
    }

    /**
     * @param int $id
     * @param array $data
     * @param int $user_id
     * @return int
     */
    public static function createReport(int $id, array $data, int $user_id) : int
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $data['user_id'] = $user_id;
        $report = $comment->reports()->create($data);
        return $report->id;
    }

    /**
     * @param int $id
     * @param int $rating_score
     * @param int $user_id
     * @return NoContentResource
     */
    public static function updateRating(int $id, int $rating_score, int $user_id) : NoContentResource
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->rating_users()->sync([$user_id => ['rating_score' => $rating_score]], false);
        return new NoContentResource();
    }

    /**
     * @param int $id
     * @param int $user_id
     * @return NoContentResource
     */
    public static function destroyRating(int $id, int $user_id) : NoContentResource
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->rating_users()->detach([$user_id]);
        return new NoContentResource();
    }
}
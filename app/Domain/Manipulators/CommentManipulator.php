<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 16:26
 */

namespace App\Domain\Manipulators;


use App\Comments\Comment;
use App\Comments\ICommentable;
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
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $sub_comment = self::createNewComment($comment, $data, $user_id);
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

    /**
     * Creates a Comment without storing it in the database.
     * Also fetches a sentiment from the ILAI API
     *
     * @param ICommentable $commentable
     * @param array $data
     * @param int $user_id
     * @return Comment
     * @throws InternalServerError
     */
    public static function createNewComment(ICommentable $commentable, array $data, int $user_id) : Comment
    {
        $user = UserRepository::getByIdOrThrowError($user_id);
        $new_comment = new Comment();
        $new_comment->fill($data);
        $new_comment->user_id = $user->id;
        $new_comment->sentiment = IlaiApi::getSentimentForText($new_comment->content);
        if(!$commentable->comments()->save($new_comment))
            throw new InternalServerError("Could not create a comment with the given data.");
        $new_comment->tags()->sync($data['tags']);
        IlaiApi::sendTags($new_comment->content, $new_comment->tags()->pluck('name')->all());
        return $new_comment;
    }
}
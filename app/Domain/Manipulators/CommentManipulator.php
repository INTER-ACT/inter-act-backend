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
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;

class CommentManipulator
{
    /**
     * @param int $id
     * @throws ApiException
     */
    public static function delete(int $id) : void
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        if(!$comment->delete())
            throw new ApiException(ApiExceptionMeta::getAInternalServerError(), 'Comment with id ' . $id . ' could not be deleted.');
    }

    /**
     * @param int $id
     * @param array $data
     * @return int
     */
    public static function createComment(int $id, array $data) : int  //TODO: remove user_id in docs?
    {
        $user = \Auth::user();
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $sub_comment = new Comment();
        $sub_comment->fill([$data]);
        $sub_comment->user_id = $user->id;
        $comment->comments()->save($sub_comment);
        return $sub_comment->id;
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
     */
    public static function updateRating(int $id, int $rating_score, int $user_id)   //TODO: return type
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->rating_users()->attach($user_id, ['rating_score' => $rating_score]);
    }

    /**
     * @param int $id
     * @param int $user_id
     * @return void
     */
    public static function destroyRating(int $id, int $user_id) : void
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->rating_users()->detach([$user_id]);
    }

    /**
     * @param int $id
     * @param int $tag_id
     */
    public static function addTag(int $id, int $tag_id) //TODO: remove addTag() if unnecessary or update params in docs
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($id);
        $comment->tags()->attach($tag_id);
    }
}
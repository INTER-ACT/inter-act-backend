<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 09:45
 */

namespace App\Domain;

use App\Comments\Comment;
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CommentResources\CommentResource;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\RatingResources\CommentRatingResource;
use App\Tags\Tag;
use Mockery\Exception;

class CommentRepository implements IRestRepository
{
    use CustomPaginationTrait;
    use RepositoryFilterTrait;

    const SUB_COMMENT_SORT_FIELD = 'created_at';
    const SUB_COMMENT_SORT_DIRECTION = 'asc';

    /**
     * @return string
     */
    public function getRestResourcePath()
    {
        return "/comments";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return "comments";
    }

    /**
     * @return string
     */
    function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }

    /**
     * @param PageRequest $pageRequest
     * @return CommentCollection
     */
    public function getAll(PageRequest $pageRequest)
    {
        $comments = Comment::orderBy('created_at', 'desc')->paginate($pageRequest->perPage, ['*'], 'start', $pageRequest->pageNumber);
        $this->updatePagination($comments);
        return new CommentCollection($comments);
    }

    /**
     * @param int $id
     * @return CommentResource
     */
    public function getById(int $id) : CommentResource
    {
        return new CommentResource(self::getCommentByIdOrThrowError($id));
        //return new CommentResource(Comment::with(['parent', 'tags'])->find($id));
    }

    /**
     * @param int $id
     * @param PageRequest $pageRequest
     * @return CommentCollection
     */
    public function getComments(int $id, PageRequest $pageRequest) : CommentCollection
    {
        $parent = self::getCommentByIdOrThrowError($id);
        $comments = $parent->comments()
            ->orderBy(self::SUB_COMMENT_SORT_FIELD, self::SUB_COMMENT_SORT_DIRECTION)
            ->paginate($pageRequest->perPage, ['*'], 'start', $pageRequest->pageNumber);
        return new CommentCollection($this->updatePagination($comments));
    }

    /**
     * @param int $id
     * @param int $user_id
     * @return CommentRatingResource
     */
    public function getRating(int $id, int $user_id) : CommentRatingResource
    {
        return (new CommentRatingResource(Comment::select('id')->with(['rating_users'])->find($id)));
    }

    /**
     * @param int $id
     * @return ReportCollection
     */
    public function getReports(int $id) : ReportCollection
    {
        return new ReportCollection(Comment::find($id)->reports);
    }

    /**
     * @param string $text
     * @return TagCollection
     */
    public function getTagRecommendations(string $text) : TagCollection
    {
        return new TagCollection(collect(IlaiApi::getTagsForText($text)));
    }

    /**
     * @param int $id
     * @return Comment
     * @throws ApiException
     */
    public static function getCommentByIdOrThrowError(int $id) : Comment
    {
        $comment = Comment::find($id);
        if($comment === null)
            throw new ResourceNotFoundException('Comment with id ' . $id . ' not found.');
        return $comment;
    }
}
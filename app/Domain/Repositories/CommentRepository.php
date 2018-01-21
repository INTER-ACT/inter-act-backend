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
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CommentResources\CommentResource;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\RatingResources\CommentRatingResource;
use Mockery\Exception;

class CommentRepository implements IRestRepository   //TODO: Exceptions missing?
{
    //TODO: according to functional specification: sortable by popularity
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
        $comments = Comment::orderBy('created_at')->paginate($pageRequest->getPerPage(), ['*'], 'start', $pageRequest->getPageNumber());
        $this->updatePagination($comments);
        return new CommentCollection($comments);
    }

    /**
     * @param int $id
     * @param array $fields
     * @return mixed
     */
    public function getById(int $id, array $fields = null)    //TODO: remove pageRequest in docs
    {
        if(!isset($fields) or $fields = []) //no filters
            return new CommentResource(Comment::with(['parent', 'tags'])->find($id));
        array_push($fields, 'id');
        array_push($fields, 'commentable_id');
        array_push($fields, 'commentable_type');

        $change_values = ['author' => 'user_id', 'rating' => null, 'parent' => 'commentable'];
        $possible_relations = ['tags', 'rating', 'comments', 'commentable'];
        $relations = [];
        $select_fields = $this->mapFilters($fields, $change_values, $possible_relations, $relations);
        //throw new Exception(implode($relations));
        return (new CommentResource(Comment::select($select_fields)->with($relations)->find($id)))->addToFieldList($fields);
    }

    /**
     * @param int $id
     * @return CommentCollection
     */
    public function getComments(int $id) : CommentCollection
    {
        return new CommentCollection(Comment::select('id')->orderBy(self::SUB_COMMENT_SORT_FIELD, self::SUB_COMMENT_SORT_DIRECTION)->find($id)->comments);
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
     * @param int $id
     * @return Comment
     * @throws ApiException
     */
    public static function getCommentByIdOrThrowError(int $id) : Comment
    {
        $comment = Comment::find($id);
        if($comment === null)
            throw new ApiException(ApiExceptionMeta::getRequestResourceNotFound(), 'Comment with id ' . $id . ' not found.');
        return $comment;
    }
}
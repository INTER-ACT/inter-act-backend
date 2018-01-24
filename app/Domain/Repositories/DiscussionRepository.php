<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 14.01.18
 * Time: 09:58
 */

namespace App\Domain;


use App\Discussions\Discussion;
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\DiscussionResources\DiscussionResource;
use App\Http\Resources\DiscussionResources\DiscussionStatisticsResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator as IPaginator;

class DiscussionRepository implements IRestRepository   //TODO: Exceptions missing?
{
    use CustomPaginationTrait;

    const DEFAULT_SORT_FIELD = 'popularity';
    const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * @return string
     */
    public function getRestResourcePath()
    {
        return "/discussions";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return "discussions";
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
     * @param string|null $sort_by
     * @param string|null $sort_dir
     * @param int|null $tag_id
     * @return DiscussionCollection
     */
    public function getAll(PageRequest $pageRequest, string $sort_by = null, string $sort_dir = null, int $tag_id = null) : DiscussionCollection
    {
        if(!isset($sort_by))
            $sort_by = self::DEFAULT_SORT_FIELD;
        if(!isset($sort_dir))
            $sort_dir = self::DEFAULT_SORT_DIRECTION;
        $query = Discussion::active();
        if(isset($tag_id))
            $query->whereHas('tags', function($tag) use($tag_id){
                $tag->where('tag_id', '=', $tag_id);
            });
        $query->with('amendments:id', 'comments:id' );

        //TODO: following code could be in method?
        $discussions = $this->queryToPaginatedCollection($query, $pageRequest, $sort_by, $sort_dir);
        return new DiscussionCollection($discussions);
    }

    /**
     * @param int $id
     * @return DiscussionResource
     * @throws ApiException
     */
    public function getById(int $id) : DiscussionResource
    {
        $discussion = $this->getDiscussionByIdOrThrowError($id);
        return new DiscussionResource($discussion);
    }

    /**
     * @param int $id
     * @param string $sort_by
     * @param string $sort_dir
     * @param PageRequest $pageRequest
     * @return AmendmentCollection
     * @throws ApiException
     */
    public function getAmendments(int $id, string $sort_by = self::DEFAULT_SORT_FIELD, string $sort_dir = self::DEFAULT_SORT_DIRECTION, PageRequest $pageRequest) : AmendmentCollection
    {
        $discussion = $this->getDiscussionByIdOrThrowError($id);
        $relation = $discussion->amendments();
        $amendments = $this->queryToPaginatedCollection($relation->getQuery(), $pageRequest, $sort_by, $sort_dir);
        return new AmendmentCollection($amendments);
    }

    /**
     * @param int $id
     * @param PageRequest $pageRequest
     * @return CommentCollection
     */
    public function getComments(int $id, PageRequest $pageRequest) : CommentCollection
    {
        $discussion = $this->getDiscussionByIdOrThrowError($id);
        $comments = $discussion->comments();
        $comments = $this->updatePagination($comments->paginate($pageRequest->perPage, ['*'], 'start', $pageRequest->pageNumber));
        return new CommentCollection($comments);
    }

    /**
     * @param int $id
     * @return DiscussionStatisticsResource
     */
    public function getStatistics(int $id) : DiscussionStatisticsResource   //TODO: delete if not needed (now an action in ActionRepository?)
    {
        return new DiscussionStatisticsResource(['not implemented!']);
    }

    /**
     * @param Builder $query
     * @param PageRequest $pageRequest
     * @param string $sort_by
     * @param string $sort_dir
     * @return IPaginator
     */
    protected function queryToPaginatedCollection(Builder $query, PageRequest $pageRequest, string $sort_by, string $sort_dir) : IPaginator
    {
        $sort_dir = (strtoupper($sort_dir) == 'ASC') ? $sort_dir : self::DEFAULT_SORT_DIRECTION;
        $sort_by = (strtolower($sort_by) == 'chronological') ? 'created_at' : self::DEFAULT_SORT_FIELD;

        if($sort_by == self::DEFAULT_SORT_FIELD) {
            $collection = ($sort_dir == 'asc') ? $query->get()->sortBy('activity') : $query->get()->sortByDesc('activity');
            $collection = $this->paginate($collection, $pageRequest->perPage, $pageRequest->pageNumber);
        }
        else
            $collection = $query->orderBy('created_at', $sort_dir)->paginate($pageRequest->perPage, ['*'], 'start', $pageRequest->pageNumber);
        return $this->updatePagination($collection);
    }

    /**
     * @param int $id
     * @return Discussion
     * @throws ApiException
     */
    public static function getDiscussionByIdOrThrowError(int $id) : Discussion
    {
        $discussion = Discussion::find($id);
        if($discussion === null)
            throw new ApiException(ApiExceptionMeta::getRequestResourceNotFound(), 'Discussion with id ' . $id . ' not found.');
        return $discussion;
    }
}
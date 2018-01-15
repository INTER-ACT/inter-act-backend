<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 14.01.18
 * Time: 09:58
 */

namespace App\Domain;


use App\Discussions\Discussion;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use Mockery\Exception;

class DiscussionRepository implements IRestRepository
{
    use CustomPaginationTrait;

    const DEFAULT_SORT_FIELD = 'popularity';
    const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * @return string
     */
    function getRestResourcePath()
    {
        return "/discussions";
    }

    /**
     * @return string
     */
    function getRestResourceName()
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
     * @param string $sort_field
     * @param string $sort_dir
     * @param int|null $tag_id
     * @return DiscussionCollection
     */
    public function getAll(PageRequest $pageRequest, string $sort_field = self::DEFAULT_SORT_FIELD, string $sort_dir = self::DEFAULT_SORT_DIRECTION, int $tag_id = null)
    {
        $sort_dir = (strtoupper($sort_dir) == 'ASC') ? $sort_dir : self::DEFAULT_SORT_DIRECTION;
        $sort_field = (strtolower($sort_field) == 'chronological') ? 'created_at' : self::DEFAULT_SORT_FIELD;

        $query = Discussion::select();
        if(isset($tag_id))
            $query->whereHas('tags', function($tag) use($tag_id){
                $tag->where('tag_id', '=', $tag_id);
            });
        $query->with('amendments:id');

        //TODO: following code could be in method?
        if($sort_field == self::DEFAULT_SORT_FIELD) {
            $discussionCollection = ($sort_dir == 'asc') ? $query->get()->sortBy('activity') : $query->get()->sortByDesc('activity');
            $discussions = $this->paginate($discussionCollection, $pageRequest->getPerPage(), $pageRequest->getPageNumber());
        }
        else
            $discussions = $query->orderBy('created_at', $sort_dir)->paginate($pageRequest->getPerPage(), ['*'], 'start', $pageRequest->getPageNumber());
        return new DiscussionCollection($this->updatePagination($discussions));
    }
}
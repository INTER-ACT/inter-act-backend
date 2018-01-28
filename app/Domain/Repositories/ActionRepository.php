<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:40
 */

namespace App\Domain;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\CommentRating;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Domain\EntityRepresentations\CommentRatingRepresentation;
use App\Domain\EntityRepresentations\MultiAspectRatingRepresentation;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Http\Resources\GeneralResources\SearchResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResourceData;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResource;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResource;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResourceData;
use App\Http\Resources\StatisticsResources\RatingStatisticsResource;
use App\Http\Resources\StatisticsResources\RatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResource;
use App\MultiAspectRating;
use App\RatingAspectRating;
use App\Reports\Report;
use App\Tags\Tag;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;

class ActionRepository implements IRestRepository   //TODO: Exceptions missing?
{
    use CustomPaginationTrait;

    const SEARCH_TYPE_TAG = 'tag';
    const SEARCH_TYPE_CONTENT = 'content';

    const SEARCH_CONTENT_TYPE_DISCUSSIONS = 'discussions';
    const SEARCH_CONTENT_TYPE_AMENDMENTS = 'amendments';
    const SEARCH_CONTENT_TYPE_SUBAMENDMENTS = 'subamendments';
    const SEARCH_CONTENT_TYPE_COMMENTS = 'comments';

    /**
     * @return string
     */
    public function getRestResourcePath()   //TODO: IRestRepository is useless here
    {
        return "/search";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return "search";
    }

    /**
     * @return string
     */
    public function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }

    /**
     * @param string $text
     * @param PageRequest $pageRequest
     * @param string|null $type
     * @param array|null $content_types
     * @return SearchResource
     */
    public function searchArticlesByText(string $text, PageRequest $pageRequest, string $type = null, array $content_types = null) : SearchResource
    {
        $all = $content_types === null || sizeof($content_types) == 0;
        $search_discussions = $all || in_array(self::SEARCH_CONTENT_TYPE_DISCUSSIONS, $content_types);
        $search_amendments = $all || in_array(self::SEARCH_CONTENT_TYPE_AMENDMENTS, $content_types);
        $search_subamendments = $all || in_array(self::SEARCH_CONTENT_TYPE_SUBAMENDMENTS, $content_types);
        $search_comments = $all || in_array(self::SEARCH_CONTENT_TYPE_COMMENTS, $content_types);

        $search_results = [];
        if($type != self::SEARCH_TYPE_CONTENT)
            $search_results = array_merge($search_results, $this->getTagSearchResult($text, $search_discussions, $search_amendments, $search_subamendments, $search_comments));
        if($type != self::SEARCH_TYPE_TAG)
            $search_results = array_merge($search_results, $this->getContentSearchResult($text, $search_discussions, $search_amendments, $search_subamendments, $search_comments));
        $search_results = array_map("unserialize", array_unique(array_map("serialize", $search_results)));
        $search_results = $this->paginate(collect($search_results), $pageRequest->perPage, $pageRequest->pageNumber);
        $search_results = $this->updatePagination($search_results);
        return new SearchResource($search_results);
    }

    /**
     * @param null $start_date
     * @param null $end_date
     * @return GeneralActivityStatisticsResource
     * @throws InvalidValueException
     */
    public function getGeneralActivityStatistics($start_date = null, $end_date = null) : GeneralActivityStatisticsResource
    {
        if(!isset($start_date))
            $start_date = Carbon::createFromDate(2017, 1, 1, 2);
        else
            $start_date = Carbon::createFromFormat('Y-m-d', $start_date);
        if(!isset($end_date))
            $end_date = now();
        else
            $end_date = Carbon::createFromFormat('Y-m-d', $end_date);
        if($start_date >= $end_date)
            throw new InvalidValueException('The start_date has to be greater than the end_date.');
        $discussions = $this->getDiscussionsBetweenDates($start_date, $end_date);
        $amendments = $this->getDiscussionsBetweenDates($start_date, $end_date);
        $sub_amendments = $this->getSubAmendmentsBetweenDates($start_date, $end_date);
        $comments = $this->getCommentsBetweenDates($start_date, $end_date);
        $reports = $this->getReportsBetweenDates($start_date, $end_date);
        $comment_ratings = $this->getCommentRatingsBetweenDates($start_date, $end_date);
        $ratings = $this->getMultiAspectRatingsBetweenDates($start_date, $end_date);
        $final_array = array_merge($discussions, $amendments, $sub_amendments, $comments, $reports, $ratings, $comment_ratings);
        return new GeneralActivityStatisticsResource($final_array);
    }

    public function getUserActivityStatisticsResource(int $user_id = null) : UserActivityStatisticsResource
    {
        $amendment_count = DB::select('SELECT users.id as user_id, discussions.id as discussion_id, COUNT(*) from amendments
          JOIN discussions on amendments.discussion_id = discussions.id
          JOIN users on amendments.user_id = users.id
          GROUP BY users.id, discussions.id');
        $users = User::select('id')->get()->transform(function($item){
            return $item->getResourcePath();
        });
        $discussions = Discussion::select('id', 'title')->get()->transform(function($item){
            return [$item->getResourcePath(), $item->title];
        });
        $total_array = [];
        foreach ($users as $user)
        {
            foreach ($discussions as $discussion)
            {
                $total_array = array_merge($total_array, [[$user, $discussion[0], $discussion[1], 9]]);
            }
        }
        /*$users->transform(function($item){
            return new UserActivityStatisticsResourceData($item->user->getResourcePath(), $item->discussion->getResourcePath, $item->discussion->title, 10);
        });*/
        return new UserActivityStatisticsResource($total_array);
    }

    public function getRatingStatisticsResource() : RatingStatisticsResource
    {
        $ratings = RatingAspectRating::select('ratable_rating_aspect_id', 'created_at as date', 'user_id')->with(['user', 'ratable_rating_aspect:id,rating_aspect_id,ratable_id,ratable_type', 'ratable_rating_aspect.ratable', 'ratable_rating_aspect.rating_aspect:id,name'])->get()
            ->transform(function($item, $key) {
                return (new RatingStatisticsResourceData($item->date, $item->user, $item->ratable_rating_aspect->ratable->getResourcePath(), $item->ratable_rating_aspect->rating_aspect->name))->toArray();
            })->toArray();

        return new RatingStatisticsResource($ratings);
    }

    public function getCommentRatingStatisticsResource() : CommentRatingStatisticsResource
    {
        $comments = Comment::select('id', 'sentiment', 'created_at')->with(['rating_users:id,year_of_birth'])->orderBy('created_at')->get();
        $comments = $comments->transform(function($item){
            $rating_users = $item->rating_users;
            $pos_ratings = $rating_users->filter(function($user, $key){
                return $user->pivot->rating_score == 1;
            })->pluck('year_of_birth')->toArray();
            rsort($pos_ratings);
            $neg_ratings = $rating_users->filter(function($user, $key){
                return $user->pivot->rating_score == -1;
            })->pluck('year_of_birth')->toArray();
            rsort($neg_ratings);

            $current_year = (int)(date("Y"));
            $pos_rating_count = sizeof($pos_ratings);
            $neg_rating_count = sizeof($neg_ratings);
            if($pos_rating_count == 0)
            {
                $age_q1_pos = 0;
                $age_q2_pos = 0;
                $age_q3_pos = 0;
            }
            else {
                /*$pos_years = array_map(function ($item) {
                    return ($item === null) ? 0 : $item->user->year_of_birth;
                }, $pos_ratings);*/
                $pos_age_count = sizeof($pos_ratings);   //may be the same as $pos_rating_count but not entirely sure
                $age_q1_pos = $current_year - $pos_ratings[(int)($pos_age_count * 0.25)]; //actually a bit more complex (with decimal places)
                $age_q2_pos = $current_year - $pos_ratings[(int)($pos_age_count * 0.5)];
                $age_q3_pos = $current_year - $pos_ratings[(int)($pos_age_count * 0.75)];
            }
            if($neg_rating_count == 0)
            {
                $age_q1_neg = 0;
                $age_q2_neg = 0;
                $age_q3_neg = 0;
            }
            else {
                /*$neg_years = array_map(function ($item) {
                    return ($item === null) ? 0 : $item->user->year_of_birth;
                }, $neg_ratings);*/
                $neg_age_count = sizeof($neg_ratings);   //may be the same as $neg_rating_count but not entirely sure
                $age_q1_neg = $current_year - $neg_ratings[(int)($neg_age_count * 0.25)]; //actually a bit more complex (with decimal places)
                $age_q2_neg = $current_year - $neg_ratings[(int)($neg_age_count * 0.5)];
                $age_q3_neg = $current_year - $neg_ratings[(int)($neg_age_count * 0.75)];
            }
            return (new CommentRatingStatisticsResourceData($item->getResourcePath(), $pos_rating_count, $neg_rating_count, $age_q1_pos, $age_q2_pos, $age_q3_pos, $age_q1_neg, $age_q2_neg, $age_q3_neg, $item->sentiment))->toArray();
        })->toArray();
        return new CommentRatingStatisticsResource($comments);
    }

    public function getObjectActivityStatisticsResource() : ActionStatisticsResource
    {
        $discussions = Discussion::select('id', 'title')->get()->transform(function($item){
            return (new ActionStatisticsResourceData($item->getResourcePath(), $item->title, [4, 1, 2, 6]))->toArray();
        })->toArray();
        $tags = Tag::select('id', 'name')->get()->transform(function($item){
            return (new ActionStatisticsResourceData($item->getResourcePath(), $item->name, [5, 3, 1, 0]))->toArray();
        })->toArray();
        $header = [
            'Diskussion/Tag',
            'Titel/Name',
            'Quartal 1 2017',
            'Quartal 2 2017',
            'Quartal 3 2017',
            'Quartal 4 2017'
        ];
        $action_resource_data = array_merge($discussions, $tags);

        return new ActionStatisticsResource($header, $action_resource_data);
    }

    //region searchHelpers
    /**
     * @param string $search_term
     * @param bool $search_discussions
     * @param bool $search_amendments
     * @param bool $search_subamendments
     * @param bool $search_comments
     * @return array
     */
    protected function getContentSearchResult(string $search_term, bool $search_discussions = true, bool $search_amendments = true, bool $search_subamendments = true, bool $search_comments = true) : array
    {
        if($search_discussions) $search_result = $this->searchDiscussionsByContent($search_term);
        else $search_result = [];
        if($search_amendments) $search_result = isset($search_result) ? array_merge($search_result, $this->searchAmendmentsByContent($search_term)) : $this->searchAmendmentsByContent($search_term);
        if($search_subamendments) $search_result = isset($search_result) ? array_merge($search_result, $this->searchSubAmendmentsByContent($search_term)) : $this->searchSubAmendmentsByContent($search_term);
        if($search_comments) $search_result = isset($search_result) ? array_merge($search_result, $this->searchCommentsByContent($search_term)) : $this->searchCommentsByContent($search_term);
        return $search_result;
    }

    /**
     * @param string $search_term
     * @param bool $search_discussions
     * @param bool $search_amendments
     * @param bool $search_subamendments
     * @param bool $search_comments
     * @return array
     */
    protected function getTagSearchResult(string $search_term, bool $search_discussions = true, bool $search_amendments = true, bool $search_subamendments = true, bool $search_comments = true) : array
    {
        if($search_discussions) $search_result = $this->searchDiscussionsByTag($search_term);
        else $search_result = [];
        if($search_amendments) $search_result = isset($search_result) ? array_merge($search_result, $this->searchAmendmentsByTag($search_term)) : $this->searchAmendmentsByTag($search_term);
        if($search_subamendments) $search_result = isset($search_result) ? array_merge($search_result, $this->searchSubAmendmentsByTag($search_term)) : $this->searchSubAmendmentsByTag($search_term);
        if($search_comments) $search_result = isset($search_result) ? array_merge($search_result, $this->searchCommentsByTag($search_term)) : $this->searchCommentsByTag($search_term);
        return $search_result;
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchDiscussionsByContent(string $search_term) : array
    {
        return Discussion::select(['id'])->where('title', 'LIKE', '%' . $search_term . '%')
            ->orWhere('law_text', 'LIKE', '%' . $search_term . '%')
            ->orWhere('law_explanation', 'LIKE', '%' . $search_term . '%')->get()->all()    ;
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchDiscussionsByTag(string $search_term) : array
    {
        return Discussion::select(['id'])->whereHas('tags', function(Builder $query) use($search_term){
            $query->where('name', 'LIKE', '%' . $search_term . '%');
        })->get()->all();
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchAmendmentsByContent(string $search_term) : array
    {
        return Amendment::select(['id'])->where('updated_text', 'LIKE', '%' . $search_term . '%')
            ->orWhere('explanation', 'LIKE', '%' . $search_term . '%')->get()->all();
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchAmendmentsByTag(string $search_term) : array
    {
        return Amendment::select(['id'])->whereHas('tags', function(Builder $query) use($search_term){
            $query->where('name', 'LIKE', '%' . $search_term . '%');
        })->get()->all();
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchSubAmendmentsByContent(string $search_term) : array
    {
        return SubAmendment::select(['id'])->where('updated_text', 'LIKE', '%' . $search_term . '%')
            ->orWhere('explanation', 'LIKE', '%' . $search_term . '%')->get()->all();
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchSubAmendmentsByTag(string $search_term) : array
    {
        return SubAmendment::select(['id'])->whereHas('tags', function(Builder $query) use($search_term){
            $query->where('name', 'LIKE', '%' . $search_term . '%');
        })->get()->all();
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchCommentsByContent(string $search_term) : array
    {
        return Comment::select(['id'])->where('content', 'LIKE', '%' . $search_term . '%')->get()->all();
    }

    /**
     * @param string $search_term
     * @return array
     */
    protected function searchCommentsByTag(string $search_term) : array
    {
        return Comment::select(['id'])->whereHas('tags', function(Builder $query) use($search_term){
            $query->where('name', 'LIKE', '%' . $search_term . '%');
        })->get()->all();
    }
    //endregion

    //region generalActivityHelpers
    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    protected function getDiscussionsBetweenDates($start_date, $end_date) : array
    {
        $discussions = Discussion::select('id', 'created_at as date', 'user_id', 'title as extra')->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user'])->get();
        return GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($discussions);
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    protected function getAmendmentsBetweenDates($start_date, $end_date) : array
    {
        $amendments = Amendment::select('id', 'discussion_id', 'created_at as date', 'user_id')->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user'])->get();
        return GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($amendments);
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    protected function getSubAmendmentsBetweenDates($start_date, $end_date) : array
    {
        $sub_amendments = SubAmendment::select('id', 'amendment_id', 'created_at as date', 'user_id')->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user'])->get();
        return GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($sub_amendments);
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    protected function getCommentsBetweenDates($start_date, $end_date) : array
    {
        $comments = Comment::select('id', 'created_at as date', 'user_id')->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user'])->get();
        return GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($comments);
    }

    /**
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    protected function getReportsBetweenDates(Carbon $start_date, Carbon $end_date) : array
    {
        $reports = Report::select('id', 'created_at as date', 'user_id')->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user'])->get();
        return GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($reports);

    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    protected function getCommentRatingsBetweenDates($start_date, $end_date) : array
    {
        $comment_ratings = CommentRating::where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user', 'comment'])->get();
        return $comment_ratings->transform(function(CommentRating $item) {
            /** @var User $user */
            $user = $item->user;
            return new GeneralActivityStatisticsResourceData(GeneralActivityStatisticsResource::getStatisticsType($item->getType()), $item->created_at, $user->getSex(), $user->postal_code, $user->job, $user->graduation, $user->getAge(), $item->getResourcePath(), $item->getApiFriendlyRating());
        })->all();
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    protected function getMultiAspectRatingsBetweenDates($start_date, $end_date) : array
    {
        $ratings = MultiAspectRating::where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user', 'ratable'])->get();
        return $ratings->transform(function(MultiAspectRating $item){
            /** @var User $user */
            $user = $item->user;
            return new GeneralActivityStatisticsResourceData(GeneralActivityStatisticsResource::getStatisticsType($item->getType()), $item->created_at, $user->getSex(), $user->postal_code, $user->job, $user->graduation, $user->getAge(), $item->ratable->getRatingPath(), implode(',', $item->getRatedAspects()));
        })->all();
    }
    //endregion
}
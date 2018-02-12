<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:40
 */

namespace App\Domain;

use App\Amendments\Amendment;
use App\Amendments\IRatable;
use App\Amendments\SubAmendment;
use App\CommentRating;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Domain\EntityRepresentations\RelevantDiscussion;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\GeneralResources\SearchResource;
use App\Http\Resources\GraduationListResource;
use App\Http\Resources\JobListResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResourceData;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResource;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResource;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResourceData;
use App\Http\Resources\StatisticsResources\MultiAspectRatingStatisticsResource;
use App\Http\Resources\StatisticsResources\MultiAspectRatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResource;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResourceData;
use App\Model\RestModel;
use App\MultiAspectRating;
use App\Reports\Report;
use App\Tags\Tag;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ActionRepository implements IRestRepository
{
    use CustomPaginationTrait;

    const JOB_LIST = ['Universitäts-Professor', 'Lehrer', 'Schüler'];
    const GRADUATION_LIST = ['Grund-/Volksschule', 'Pflichtschule', 'Reife- und Diplomprüfung', 'Bachelor-Studium', 'Master-Studium', 'Doktor'];

    const SEARCH_TYPE_TAG = 'tag';
    const SEARCH_TYPE_CONTENT = 'content';

    const SEARCH_CONTENT_TYPE_DISCUSSIONS = 'discussions';
    const SEARCH_CONTENT_TYPE_AMENDMENTS = 'amendments';
    const SEARCH_CONTENT_TYPE_SUBAMENDMENTS = 'subamendments';
    const SEARCH_CONTENT_TYPE_COMMENTS = 'comments';

    /**
     * @return string
     */
    public function getRestResourcePath()
    {
        return "/";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return "Statistics";
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
     */
    public function getGeneralActivityStatistics($start_date = null, $end_date = null) : GeneralActivityStatisticsResource
    {
        self::mapDateInputToDates($start_date, $end_date);
        $discussions = $this->getDiscussionsBetweenDates($start_date, $end_date);
        $amendments = $this->getAmendmentsBetweenDates($start_date, $end_date);
        $sub_amendments = $this->getSubAmendmentsBetweenDates($start_date, $end_date);
        $comments = $this->getCommentsBetweenDates($start_date, $end_date);
        $reports = $this->getReportsBetweenDates($start_date, $end_date);
        $comment_ratings = $this->getCommentRatingsBetweenDates($start_date, $end_date);
        $ratings = $this->getMultiAspectRatingsBetweenDates($start_date, $end_date);
        $final_array = array_merge($discussions, $amendments, $sub_amendments, $comments, $reports, $ratings, $comment_ratings);
        return new GeneralActivityStatisticsResource($final_array);
    }

    /**
     * @param null $start_date
     * @param null $end_date
     * @return MultiAspectRatingStatisticsResource
     */
    public function getRatingStatisticsResource($start_date = null, $end_date = null) : MultiAspectRatingStatisticsResource
    {
        self::mapDateInputToDates($start_date, $end_date);
        $ratings = MultiAspectRating::where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->orderBy('created_at', 'desc')->with(['user', 'ratable'])->get();
        $ratings = $ratings->transform(function(MultiAspectRating $item){
            /** @var IRatable $ratable */
            $ratable = $item->ratable;
            return (new MultiAspectRatingStatisticsResourceData($item->created_at, $item->user, $ratable->getId(), $ratable->getApiFriendlyTypeGer(), $ratable->getResourcePath(), $item->getRatedAspects()))->toArray();
        })->all();
        return new MultiAspectRatingStatisticsResource($ratings);
    }

    /**
     * @param null $start_date
     * @param null $end_date
     * @return CommentRatingStatisticsResource
     */
    public function getCommentRatingStatisticsResource($start_date = null, $end_date = null) : CommentRatingStatisticsResource
    {
        self::mapDateInputToDates($start_date, $end_date);
        $comments = Comment::where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->orderBy('created_at', 'desc')->select('id', 'sentiment', 'created_at')->with(['rating_users:id,year_of_birth'])->orderBy('created_at')->get();
        $comments = $comments->transform(function($item){
            $rating_users = $item->rating_users;
            $pos_ratings = $rating_users->filter(function($user, $key){
                return $user->pivot->rating_score == 1;
            })->pluck('year_of_birth')->toArray();
            $neg_ratings = $rating_users->filter(function($user, $key){
                return $user->pivot->rating_score <= 0;
            })->pluck('year_of_birth')->toArray();
            $current_year = (int)(date("Y"));
            $pos_ratings = array_map(function($item) use($current_year){
                return $current_year - $item;
            }, $pos_ratings);
            $neg_ratings = array_map(function($item) use($current_year){
                return $current_year - $item;
            }, $neg_ratings);
            sort($pos_ratings);
            sort($neg_ratings);
            $pos_rating_count = sizeof($pos_ratings);
            $neg_rating_count = sizeof($neg_ratings);
            $q_pos = $this->getQuartiles($pos_ratings);
            $q_neg = $this->getQuartiles($neg_ratings);
            return (new CommentRatingStatisticsResourceData($item->getResourcePath(), $item->created_at, $pos_rating_count, $neg_rating_count, $item->sentiment, $q_pos[0], $q_pos[1], $q_pos[2], $q_neg[0], $q_neg[1], $q_neg[2]))->toArray();
        })->toArray();
        return new CommentRatingStatisticsResource($comments);
    }

    public function getUserActivityStatisticsResource(int $user_id = 0) : UserActivityStatisticsResource
    {
        if($user_id > 1)
            $data = $this->getUserActivity($user_id);
        else
            $data = $this->getAllUserActivity();

        return new UserActivityStatisticsResource($data);
    }

    public function getRelevantDiscussions(int $user_id) : DiscussionCollection
    {
        $user = UserRepository::getByIdOrThrowError($user_id);
        /** @var Collection $discussions1 */
        $discussions1 = $user->discussions->transform(function(Discussion $item){
            return new RelevantDiscussion($item->id, $item->getResourcePath(), $item->title, $item->created_at);
        });
        /** @var Collection $discussions2 */
        $discussions2 = $user->amendments->transform(function(Amendment $item){
            /** @var Discussion $discussion */
            $discussion = $item->discussion;
            return new RelevantDiscussion($discussion->id, $discussion->getResourcePath(), $discussion->title, $item->created_at);
        });
        /** @var Collection $discussions3 */
        $discussions3 = $user->sub_amendments->transform(function(SubAmendment $item){
            /** @var Discussion $discussion */
            $discussion = $item->amendment->discussion;
            return new RelevantDiscussion($discussion->id, $discussion->getResourcePath(), $discussion->title, $item->created_at);
        });
        /** @var Collection $discussions4 */
        $discussions4 = $user->comments->transform(function(Comment $item){
            $parent = $this->getDiscussionForPost($item);
            return new RelevantDiscussion($parent->id, $parent->getResourcePath(), $parent->title, $item->created_at);
        });
        $all = $discussions1->merge($discussions2->merge($discussions3->merge($discussions4)));
        $all->sort(function(RelevantDiscussion $a, RelevantDiscussion $b){
            return $a->user_interaction_date > $b->user_interaction_date;
        });
        return new DiscussionCollection($all);
    }

    /**
     * @param $post
     * @return Discussion
     */
    protected function getDiscussionForPost($post) : Discussion
    {
        if($post instanceof Comment)
            return $this->getDiscussionForPost($post->parent);
        if($post instanceof SubAmendment)
            return $post->amendment->discussion;
        if($post instanceof Amendment)
            return $post->discussion;
        if($post instanceof Discussion)
            return $post;
        return null;
    }

    /**
     * @return ActionStatisticsResource
     */
    public function getObjectActivityStatisticsResource() : ActionStatisticsResource
    {
        $discussions = Discussion::select('id', 'title', 'created_at')->get()->transform(function(Discussion $item){
            return (new ActionStatisticsResourceData($item->getResourcePath(), $item->title, $item->activity, $item->getActivity(now()->subMonth(), now())))->toArray();
        })->toArray();
        $tags = Tag::select('id', 'name', 'created_at')->get()->transform(function(Tag $item){
            return (new ActionStatisticsResourceData($item->getResourcePath(), $item->name, $item->activity, $item->getActivity(now()->subMonth(), now())))->toArray();
        })->toArray();
        $action_resource_data = array_merge($discussions, $tags);

        return new ActionStatisticsResource($action_resource_data);
    }

    /**
     * @return JobListResource
     */
    public function getJobList() : JobListResource
    {
        return new JobListResource(self::JOB_LIST);
    }

    /**
     * @return GraduationListResource
     */
    public function getGraduationList() : GraduationListResource
    {
        return new GraduationListResource(self::GRADUATION_LIST);
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
        $discussions = Discussion::select('id', 'created_at as date', 'user_id')->where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user'])->get();
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
        $reports = Report::where([['created_at', '>=', $start_date], ['created_at', '<=', $end_date]])->with(['user', 'reportable:id'])->get();
        return $reports->transform(function(Report $item){
            /** @var User $user */
            $user = $item->user;
            return (new GeneralActivityStatisticsResourceData($item->getId(), $item->getApiFriendlyTypeGer(), $item->created_at, $user->getSex(), $user->postal_code, $user->job, $user->graduation, $user->getAge(), $item->getResourcePath(), $item->reportable->getResourcePath()));
        })->all();
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
            return new GeneralActivityStatisticsResourceData($item->getId(), $item->getApiFriendlyTypeGer(), $item->created_at, $user->getSex(), $user->postal_code, $user->job, $user->graduation, $user->getAge(), $item->getResourcePath(), $item->getApiFriendlyRating());
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
            return new GeneralActivityStatisticsResourceData($item->getId(), $item->getApiFriendlyTypeGer(), $item->created_at, $user->getSex(), $user->postal_code, $user->job, $user->graduation, $user->getAge(), $item->ratable->getRatingPath(), implode(',', $item->getRatedAspects()));
        })->all();
    }
    //endregion

    //region commentRatingHelpers
    public function getQuartiles(array $array) : array
    {
        $size = sizeof($array);
        if($size == 0)
            return [0, 0, 0];
        if($size % 2 == 0) {
            $size += 1;
            $half = $size * 0.5 - 1;
            $quarterIndex = (int)(floor($half) * 0.5);
            $q1 = $array[$quarterIndex];
            $q2 = ($array[(int)floor($half)] + $array[(int)ceil($half)]) * 0.5;
            $q3 = $array[$size - $quarterIndex - 2];
        }
        else {
            if($size == 1)
                return [$array[0], $array[0], $array[0]];
            if($size == 3)
                return [$array[0], $array[1], $array[2]];
            $size += 1;
            $q1Float = ($size) * 0.25;
            $q1 = ($array[(int)floor($q1Float) - 1] + $array[(int)ceil($q1Float) - 1]) * 0.5;
            $q2 = $array[(int)(($size) * 0.5) - 1];
            $q3Float = ($size) * 0.75;
            $q3 = ($array[(int)floor($q3Float) - 1] + $array[(int)ceil($q3Float) - 1]) * 0.5;
        }
        return [$q1, $q2, $q3];
    }
    //endregion

    //region userActivityHelpers
    protected function getAllUserActivity() : array
    {
        $total = [];
        $users = User::all();
        foreach ($users as $user) {
            $total = array_merge($total, array_merge($this->getUserActivityDiscussions($user->id), $this->getUserActivityTags($user->id)));
        }
        return $total;
    }

    protected function getUserActivity(int $user_id) : array
    {
        return array_merge($this->getUserActivityDiscussions($user_id), $this->getUserActivityTags($user_id));
    }

    protected function getUserActivityDiscussions(int $user_id) : array
    {
        //TODO: if time, add ratings as well.
        /** @var User $user */
        $user = UserRepository::getByIdOrThrowError($user_id);
        $discussion_ids = [];
        $discussion_ids = array_merge($discussion_ids, $user->discussions()->select('id')->pluck('id')->all());
        $discussion_ids = array_merge($discussion_ids, $user->amendments()->select('id', 'discussion_id')->pluck('discussion_id')->all());
        $discussion_ids = array_merge($discussion_ids, $user->sub_amendments()->select('id', 'amendment_id')->with('amendment:id,discussion_id')->get()
            ->transform(function(SubAmendment $item) {
                return $item->amendment->discussion_id;
            })->all());
        $discussion_ids = array_merge($discussion_ids, $user->comments()->select('id', 'commentable_id', 'commentable_type')->get()->transform(function(Comment $item){
            $parent = $this->getDiscussionForPost($item);
            return $parent->id;
        })->all());
        $ids_unique = array_unique($discussion_ids);
        $discussion_sums = array_fill_keys($ids_unique, 0);
        foreach ($discussion_ids as $id)
        {
            $discussion_sums[$id]++;
        }
        return array_map(function($key, $item) use($user){
            $discussion = Discussion::select('id', 'title')->find($key);
            return (new UserActivityStatisticsResourceData($user->getResourcePath(), $discussion->getResourcePath(), $discussion->title, $item))->toArray();
        }, array_keys($discussion_sums), $discussion_sums);
    }

    protected function getUserActivityTags(int $user_id) : array
    {
        /** @var User $user */
        $user = UserRepository::getByIdOrThrowError($user_id);
        $tag_ids = [];
        $tag_ids = array_merge($tag_ids, $user->discussions()->select('id')->get()
            ->transform(function(Discussion $item){
                return $item->tags()->pluck('id')->all();
            })->flatten()->all());
        $tag_ids = array_merge($tag_ids, $user->amendments()->select('id')->get()
            ->transform(function(Amendment $item){
                return $item->tags()->pluck('id')->all();
            })->flatten()->all());
        $tag_ids = array_merge($tag_ids, $user->sub_amendments()->select('id')->get()
            ->transform(function(SubAmendment $item){
                return $item->tags()->pluck('id')->all();
            })->flatten()->all());
        $tag_ids = array_merge($tag_ids, $user->comments()->select('id')->get()
            ->transform(function(Comment $item){
                return $item->tags()->pluck('id')->all();
            })->flatten()->all());
        $ids_unique = array_unique($tag_ids);
        $tag_sums = array_fill_keys($ids_unique, 0);
        foreach ($tag_ids as $id)
        {
            $tag_sums[$id]++;
        }
        return array_map(function($key, $item) use($user){
            /** @var Tag $tag */
            $tag = Tag::select('id', 'name')->find($key);
            return (new UserActivityStatisticsResourceData($user->getResourcePath(), $tag->getResourcePath(), $tag->title, $item))->toArray();
        }, array_keys($tag_sums), $tag_sums);
    }
    //endregion

    protected function mapDateInputToDates(&$start_date, &$end_date)
    {
        if(!isset($start_date))
            $start_date = Carbon::createFromDate(2017, 1, 1, 2);
        else
            $start_date = Carbon::createFromFormat('Y-m-d', $start_date);
        if(!isset($end_date))
            $end_date = now();
        else
            $end_date = Carbon::createFromFormat('Y-m-d', $end_date);
        if($start_date > $end_date)
            throw new InvalidValueException('The start_date has to be greater than the end_date.');
    }
}
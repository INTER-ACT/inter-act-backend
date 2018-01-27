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
use App\Http\Resources\GeneralResources\SearchResource;
use App\Http\Resources\GeneralResources\SearchResourceData;
use App\Http\Resources\StatisticsResources\ActionStatisticsResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResourceData;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResource;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResource;
use App\Http\Resources\StatisticsResources\RatingStatisticsResource;
use App\Http\Resources\StatisticsResources\RatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\StatisticsResource;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResource;
use App\RatingAspectRating;
use App\Tags\Tag;
use App\User;
use Carbon\Carbon;
use DB;

class ActionRepository implements IRestRepository   //TODO: Exceptions missing?
{
    use CustomPaginationTrait;

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
     * @param string|null $content_type
     * @return SearchResource
     */
    public function searchArticlesByText(string $text, PageRequest $pageRequest, string $type = null, string $content_type = null) : SearchResource
    {
        $discussions = Discussion::where('title', 'LIKE', '%' . $text . '%')
            ->orWhere('law_text', 'LIKE', '%' . $text . '%')
            ->orWhere('law_explanation', 'LIKE', '%' . $text . '%')->get();
        $amendments = Amendment::where('updated_text', 'LIKE', '%' . $text . '%')
            ->orWhere('explanation', 'LIKE', '%' . $text . '%')->get();
        $sub_amendments = SubAmendment::where('updated_text', 'LIKE', '%' . $text . '%')
            ->orWhere('explanation', 'LIKE', '%' . $text . '%')->get();
        $comments = Comment::where('content', 'LIKE', '%' . $text . '%')->get();

        $data = new SearchResourceData($discussions, $amendments, $sub_amendments, $comments);
        return new SearchResource($data);   //TODO: implement pagination, type and post_type
    }

    public function getGeneralActivityStatistics(Carbon $start_date = null, Carbon $end_date = null) : GeneralActivityStatisticsResource
    {
        $discussions = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray(Discussion::select('id', 'created_at as date', 'user_id', 'title as extra')->with(['user'])->get());
        $amendments = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray(Amendment::select('id', 'discussion_id', 'created_at as date', 'user_id')->with(['user'])->get());
        $sub_amendments = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray(SubAmendment::select('id', 'amendment_id', 'created_at as date', 'user_id')->with(['user'])->get());
        $comments = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray(Comment::select('id', 'created_at as date', 'user_id')->with(['user'])->get());
        $reports = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray(Report::select('id', 'created_at as date', 'user_id')->with(['user'])->get());
        //return RatingAspectRating::select('ratable_rating_aspect_id', 'created_at as date', 'user_id')->with(['user', 'ratable_rating_aspect:id,rating_aspect_id,ratable_id,ratable_type', 'ratable_rating_aspect.ratable', 'ratable_rating_aspect.rating_aspect:id,name'])->get();
        $ratings_raw = RatingAspectRating::select('ratable_rating_aspect_id', 'created_at as date', 'user_id')->with(['user', 'ratable_rating_aspect:id,rating_aspect_id,ratable_id,ratable_type', 'ratable_rating_aspect.ratable', 'ratable_rating_aspect.rating_aspect:id,name'])->get()
            ->transform(function($item, $key) {
                return (new MultiAspectRatingRepresentation($item->date, $item->ratable_rating_aspect->ratable, $item->user, $item->ratable_rating_aspect->rating_aspect->name));
            });
        $ratings = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($ratings_raw);
        $comment_ratings_raw = CommentRating::with(['user', 'comment'])->get()
            ->transform(function($item, $key) {
                return new CommentRatingRepresentation($item->created_at, $item->comment, $item->user, $item->rating_score);
            });
        $comment_ratings = GeneralActivityStatisticsResource::transformCollectionToResourceDataArray($comment_ratings_raw);
        $final_array = array_merge($discussions, $amendments, $sub_amendments, $comments, $reports, $ratings, $comment_ratings);
        //return $final_array;
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
}
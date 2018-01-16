<?php

use App\CommentRating;
use App\Domain\DiscussionRepository;
use App\Domain\EntityRepresentations\CommentRatingRepresentation;
use App\Domain\EntityRepresentations\MultiAspectRatingRepresentation;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\AmendmentResources\AmendmentResource;
use App\Http\Resources\AmendmentResources\ChangeCollection;
use App\Http\Resources\AmendmentResources\ChangeResource;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CommentResources\CommentResource;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\DiscussionResources\DiscussionResource;
use App\Http\Resources\GeneralResources\SearchResource;
use App\Http\Resources\GeneralResources\SearchResourceData;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\PostResources\ReportResource;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\PostResources\TagResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResource;
use App\Http\Resources\StatisticsResources\ActionStatisticsResourceData;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResource;
use App\Http\Resources\StatisticsResources\CommentRatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResource;
use App\Http\Resources\StatisticsResources\GeneralActivityStatisticsResourceData;
use App\Http\Resources\StatisticsResources\ArrayOfActionStatisticsResourceData;
use App\Http\Resources\RatingResources\CommentRatingResource;
use App\Http\Resources\StatisticsResources\RatingStatisticsResource;
use App\Http\Resources\StatisticsResources\RatingStatisticsResourceData;
use App\Http\Resources\StatisticsResources\StatisticsResource;
use App\Http\Resources\StatisticsResources\StatisticsResourceData;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResource;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResourceData;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;
use App\Http\Resources\SubAmendmentResources\SubAmendmentResource;
use App\Http\Resources\UserResources\UserCollection;
use App\Http\Resources\UserResources\UserResource;
use App\Http\Resources\UserResources\UserStatisticsResource;
use App\RatingAspectRating;
use App\User;
use App\Discussions\Discussion;
use App\Tags\Tag;
use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Reports\Report;

use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\StreamedResponse;

//region general
Route::get('/', function ()
{
    return "Welcome to the Test-API!";
});

Route::get('/ping', function ()
{
    return "Test-API ping response";
})->middleware('auth:api');
//endregion

//region users
Route::get('/users', function(){
   return new UserCollection(User::all());
});

Route::get('/users/{user_id}', function($user_id){
    return new UserResource(User::find($user_id));
});

Route::get('/users/{user_id}/discussions', function($user_id){
    return new DiscussionCollection(User::find($user_id)->discussions);
});
//endregion

//region discussions
Route::get('/discussions', function(DiscussionRepository $repository){
    //return Discussion::find(2)->getActivity(Carbon::createFromDate(2017, 1, 1, 1), Carbon::now());
    $perPage = Input::get('count', 10);
    $pageNumber = Input::get('start', 1);
    $tag_id = Input::get('tag_id', null);
    $sorted_by = Input::get('sorted_by', '');
    $sort_dir = Input::get('sort_direction', '');
    return $repository->getAll(new PageRequest($perPage, $pageNumber), $sorted_by, $sort_dir, $tag_id);
});

Route::get('/discussions/{discussion_id}', function(int $discussion_id, DiscussionRepository $repository){
    return $repository->getById($discussion_id);
});

Route::get('/discussions/{discussion_id}/amendments', function(int $discussion_id, DiscussionRepository $repository){
    $perPage = Input::get('count', 10);
    $pageNumber = Input::get('start', 1);
    $sorted_by = Input::get('sorted_by', '');
    $sort_dir = Input::get('sort_direction', '');
    return $repository->getAmendments($discussion_id, $sorted_by, $sort_dir, new PageRequest($perPage, $pageNumber));
});

Route::get('/discussions/{discussion_id}/comments', function(int $discussion_id, DiscussionRepository $repository){
    $perPage = Input::get('count', 10);
    $pageNumber = Input::get('start', 1);
    return $repository->getComments($discussion_id, new PageRequest($perPage, $pageNumber));
});

//endregion

//region amendments
Route::get('/discussions/{discussion_id}/amendments/{amendment_id}', function(int $amendment_id){
    return new AmendmentResource(Amendment::find($amendment_id));
});

Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments', function(int $amendment_id){
    return new SubAmendmentCollection(Amendment::find($amendment_id)->sub_amendments);
});
//endregion

//region subamendments
Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{subamendment_id}', function(int $subamendment_id){
    return new SubamendmentResource(Subamendment::find($subamendment_id));
});

Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/changes', function(int $amendment_id){
    return new ChangeCollection(Amendment::find($amendment_id));
});

Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/changes/{subamendment_id}', function(int $subamendment_id){
    $subamendment = SubAmendment::find($subamendment_id);
    if($subamendment->status != SubAmendment::ACCEPTED_STATUS)
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();

    return new ChangeResource($subamendment);
});

Route::get('/subamendments/{subamendment_id}/comments', function(int $subamendment_id){
    return SubAmendment::find($subamendment_id)->comments;
});
//endregion

//region comments
Route::get('/comments', function(){
    return new CommentCollection(Comment::all());
});

Route::get('/comments/{comment_id}', function(int $comment_id){
    return new CommentResource(Comment::find($comment_id));
});

Route::get('/comments/{comment_id}/ratings', function(int $comment_id){
    return new CommentRatingResource(Comment::find($comment_id));
});
//endregion

//region tags and reports
Route::get('/tags', function(){
    return new TagCollection(Tag::all());
});

Route::get('/tags/{tag_id}', function(int $tag_id){
    return new TagResource(Tag::find($tag_id));
});

Route::get('/reports', function(){
    return new ReportCollection(Report::all());
});

Route::get('/reports/{report_id}', function(int $report_id){
    return new ReportResource(Report::find($report_id));
});
//endregion

//region search
Route::get('/search', function(\Illuminate\Http\Request $request){
    $search_term = $request->search_term;
    if($search_term == null)
        throw new ApiException(ApiExceptionMeta::getRequestInvalidValue(), 'Parameter search_term not provided.');
    $type = $request->type;
    $post_type = $request->post_type;
    $pag_start = $request->start;
    $pag_count = $request->count;
    $discussions = Discussion::where('title', 'LIKE', '%' . $search_term . '%')
        ->orWhere('law_text', 'LIKE', '%' . $search_term . '%')
        ->orWhere('law_explanation', 'LIKE', '%' . $search_term . '%')->get();
    $amendments = Amendment::where('updated_text', 'LIKE', '%' . $search_term . '%')
        ->orWhere('explanation', 'LIKE', '%' . $search_term . '%')->get();
    $sub_amendments = SubAmendment::where('updated_text', 'LIKE', '%' . $search_term . '%')
        ->orWhere('explanation', 'LIKE', '%' . $search_term . '%')->get();
    $comments = Comment::where('content', 'LIKE', '%' . $search_term . '%')->get();
    //return get_class($discussions);
    $data = new SearchResourceData($discussions, $amendments, $sub_amendments, $comments);
    return new SearchResource($data);
});
//endregion

//region statistics
Route::get('/statistics/general_activity', function(){
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
    $resource = new GeneralActivityStatisticsResource($final_array);
    $data = $resource->toArray();
    return new StreamedResponse(
        function() use($data)
        {
            // A resource pointer to the output stream for writing the CSV to
            $handle = fopen('php://output', 'w');
            foreach ($data as $row)
            {
                // Loop through the data and write each entry as a new row in the csv
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=GeneralActivityStatistics.csv'
        ]
    );
});

Route::get('/statistics/ratings', function(){
    $ratings = RatingAspectRating::select('ratable_rating_aspect_id', 'created_at as date', 'user_id')->with(['user', 'ratable_rating_aspect:id,rating_aspect_id,ratable_id,ratable_type', 'ratable_rating_aspect.ratable', 'ratable_rating_aspect.rating_aspect:id,name'])->get()
        ->transform(function($item, $key) {
            return (new RatingStatisticsResourceData($item->date, $item->user, $item->ratable_rating_aspect->ratable->getResourcePath(), $item->ratable_rating_aspect->rating_aspect->name))->toArray();
        })->toArray();

    $resource = new RatingStatisticsResource($ratings);
    $data = $resource->toArray();
    return new StreamedResponse(
        function() use($data)
        {
            // A resource pointer to the output stream for writing the CSV to
            $handle = fopen('php://output', 'w');
            foreach ($data as $row)
            {
                // Loop through the data and write each entry as a new row in the csv
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=RatingStatistics.csv'
        ]
    );
});

Route::get('/statistics/comment_ratings', function(){
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
    $resource = new CommentRatingStatisticsResource($comments);
    $data = $resource->toArray();
    return new StreamedResponse(
        function() use($data)
        {
            $handle = fopen('php://output', 'w');
            foreach ($data as $row)
            {
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=CommentRatingStatistics.csv'
        ]
    );
});

Route::get('/statistics/action', function(){
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

    $resource = new ActionStatisticsResource($header, $action_resource_data);
    $data = $resource->toArray();
    return new StreamedResponse(
        function() use($data)
        {
            $handle = fopen('php://output', 'w');
            foreach ($data as $row)
            {
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=ActionStatistics.csv'
        ]
    );
});

Route::get('/statistics/users', function(){
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
    $data = (new UserActivityStatisticsResource($total_array))->toArray();
    return new StreamedResponse(
        function() use($data)
        {
            // A resource pointer to the output stream for writing the CSV to
            $handle = fopen('php://output', 'w');
            foreach ($data as $row)
            {
                // Loop through the data and write each entry as a new row in the csv
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=UserActivityStatistics.csv'
        ]
    );
});

Route::get('/statistics/all_users_table', function(){
    $users = User::all();
    $csv_data = $users->reduce(
        function ($data, $user) {
            $data[] = [
                $user->id,
                $user->username,
                $user->email,
                $user->first_name,
                $user->last_name,
                $user->getSex(),
                $user->postal_code,
                $user->city,
                $user->job,
                $user->graduation,
                $user->getAge()
            ];
            return $data;
        },
        [
            [
                trans('id'),
                trans('username'),
                trans('email'),
                trans('first_name'),
                trans('last_name'),
                trans('gender'),
                trans('postal code'),
                trans('city'),
                trans('job'),
                trans('graduation'),
                trans('age')
            ]
        ]
    );

    return new StreamedResponse(
        function() use($csv_data)
        {
            // A resource pointer to the output stream for writing the CSV to
            $handle = fopen('php://output', 'w');
            foreach ($csv_data as $row)
            {
                // Loop through the data and write each entry as a new row in the csv
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=statistics.csv'
        ]
    );
});

Route::get('/statistics/application_statistics', function(){
    $user_count = DB::selectOne('SELECT COUNT(*) as val from users')->val;
    $avg_age = DB::selectOne('SELECT AVG(age) as val from (SELECT (YEAR(CURRENT_DATE()) - year_of_birth) as age from users) as age_table')->val;
    $male_count = DB::selectOne('SELECT COUNT(id) as val from users WHERE is_male = 1')->val;
    $female_count = DB::selectOne('SELECT COUNT(id) as val from users WHERE is_male = 0')->val;
    $discussion_count = DB::selectOne('SELECT COUNT(*) as val from discussions')->val;
    $amendment_count = DB::selectOne('SELECT COUNT(*) as val from amendments')->val;
    $sub_amendment_count = DB::selectOne('SELECT COUNT(*) as val from sub_amendments')->val;
    $ma_rating_count = DB::selectOne('SELECT COUNT(*) as val from rating_aspect_rating')->val;
    $comment_count = DB::selectOne('SELECT COUNT(*) as val from comments')->val;
    $comment_rating_count = DB::selectOne('SELECT COUNT(*) as val from comment_ratings')->val;
    $report_count = DB::selectOne('SELECT COUNT(*) as val from reports')->val;
    $resource_data = new StatisticsResourceData($user_count, $avg_age, $male_count, $female_count, $discussion_count, $amendment_count, $sub_amendment_count, $ma_rating_count, $comment_count, $comment_rating_count, $report_count);
    $resource = new StatisticsResource($resource_data);
    $data = $resource->toArray();

    return new StreamedResponse(
        function() use($data)
        {
            // A resource pointer to the output stream for writing the CSV to
            $handle = fopen('php://output', 'w');
            foreach ($data as $row)
            {
                // Loop through the data and write each entry as a new row in the csv
                fputcsv($handle, $row);
            }

            fclose($handle);
        },
        200,
        [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=statistics.csv'
        ]
    );
});

Route::get('/statistics/application_statistics_json', function(){
    $user_count = DB::selectOne('SELECT COUNT(*) as val from users')->val;
    $avg_age = DB::selectOne('SELECT AVG(age) as val from (SELECT (YEAR(CURRENT_DATE()) - year_of_birth) as age from users) as age_table')->val;
    $male_count = DB::selectOne('SELECT COUNT(id) as val from users WHERE is_male = 1')->val;
    $female_count = DB::selectOne('SELECT COUNT(id) as val from users WHERE is_male = 0')->val;
    $discussion_count = DB::selectOne('SELECT COUNT(*) as val from discussions')->val;
    $amendment_count = DB::selectOne('SELECT COUNT(*) as val from amendments')->val;
    $sub_amendment_count = DB::selectOne('SELECT COUNT(*) as val from sub_amendments')->val;
    $ma_rating_count = DB::selectOne('SELECT COUNT(*) as val from rating_aspect_rating')->val;
    $comment_count = DB::selectOne('SELECT COUNT(*) as val from comments')->val;
    $comment_rating_count = DB::selectOne('SELECT COUNT(*) as val from comment_ratings')->val;
    $report_count = DB::selectOne('SELECT COUNT(*) as val from reports')->val;
    $data = new StatisticsResourceData($user_count, $avg_age, $male_count, $female_count, $discussion_count, $amendment_count, $sub_amendment_count, $ma_rating_count, $comment_count, $comment_rating_count, $report_count);
    return (new StatisticsResource($data))->toArray();
});
//endregion


<?php

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
use App\Http\Resources\StatisticsResources\StatisticsResource;
use App\Http\Resources\StatisticsResources\StatisticsResourceData;
use App\Http\Resources\SubAmendmentResources\SubAmendmentCollection;
use App\Http\Resources\SubAmendmentResources\SubAmendmentResource;
use App\Http\Resources\UserResources\UserCollection;
use App\Http\Resources\UserResources\UserResource;
use App\Http\Resources\UserResources\UserStatisticsResource;
use App\User;
use App\Discussions\Discussion;
use App\Tags\Tag;
use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Reports\Report;

use Symfony\Component\HttpFoundation\StreamedResponse;

//region general
Route::get('/', function ()
{
    return "Welcome to the Test-API!";
});

Route::get('/ping', function ()
{
    return "Test-API ping response";
});
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
Route::get('/discussions', function(){
    return new DiscussionCollection(Discussion::all());
});

Route::get('/discussions/{discussion_id}', function(int $discussion_id){
    return new DiscussionResource(Discussion::find($discussion_id));
});

Route::get('/discussions/{discussion_id}/amendments', function(int $discussion_id){
    return new AmendmentCollection(Amendment::all()->where('discussion_id', '=', $discussion_id));
});

Route::get('/discussions/{discussion_id}/comments', function(int $discussion_id){
    return Discussion::find($discussion_id)->comments;
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
    $data = new SearchResourceData($discussions, $amendments, $sub_amendments, $comments);
    return new SearchResource($data);
});
//endregion

//region statistics
Route::get('/statistics', function(){
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

Route::get('/users/{user_id}/statistics', function($user_id){
    return new UserStatisticsResource(User::find($user_id));
});

Route::get('/users/statistics', function(){
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

Route::get('/test/statistics', function(){
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
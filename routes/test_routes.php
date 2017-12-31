<?php

use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;
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
use App\Http\Resources\UserResources\UserCollection;
use App\Http\Resources\UserResources\UserResource;
use App\Http\Resources\UserResources\UserStatisticsResource;
use App\Role;
use App\User;
use App\Discussions\Discussion;
use App\Tags\Tag;
use App\Amendments\RatingAspect;
use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Amendments\RatableRatingAspect;
use App\Comments\Comment;
use App\Reports\Report;

use App\Http\Resources;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/', function ()
{
    return "Welcome to the Test-API!";
});

Route::get('/ping', function ()
{
    return "Test-API ping response";
});

Route::get('/users', function(){
   return new UserCollection(User::all());
});

Route::get('/users/{user_id}', function($user_id){
    return new UserResource(User::find($user_id));
});

Route::get('/users/{user_id}/statistics', function($user_id){
    return new UserStatisticsResource(User::find($user_id));
});

Route::get('/users/{user_id}/discussions', function($user_id){
    return new DiscussionCollection(User::find($user_id)->discussions);
});

Route::get('/discussions', function(){
    return new DiscussionCollection(Discussion::all());
});

Route::get('/discussions/{discussion_id}', function(int $discussion_id){
    return new DiscussionResource(Discussion::find($discussion_id));
});

Route::get('/discussions/{discussion_id}/amendments', function(int $discussion_id){
    return Discussion::find($discussion_id)->amendments;
});

Route::get('/discussions/{discussion_id}/amendments/{amendment_id}', function(int $amendment_id){
    return Amendment::with(['user', 'discussion', 'sub_amendments', 'comments', 'tags', 'ratings', 'rating_aspects', 'reports'])->where('id', $amendment_id)->get();
});

Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments', function(int $amendment_id){
    return Amendment::find($amendment_id)->sub_amendments;
});

Route::get('/subamendments/{subamendment_id}', function(int $subamendment_id){
    return SubAmendment::with(['user', 'amendment', 'comments', 'tags', 'ratings', 'rating_aspects', 'reports'])->where('id', $subamendment_id)->get();
});

Route::get('/subamendments/{subamendment_id}/comments', function(int $subamendment_id){
    return SubAmendment::find($subamendment_id)->comments;
});

Route::get('/discussions/{discussion_id}/comments', function(int $discussion_id){
    return Discussion::find($discussion_id)->comments;
});

Route::get('/comments', function(){
    return new CommentCollection(Comment::all());
});

Route::get('/comments/{comment_id}', function(int $comment_id){
    return new CommentResource(Comment::find($comment_id));
});

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

Route::get('/statistics', function(){
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


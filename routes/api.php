<?php

use App\Http\Resources\UserResources\SelfResource;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', 'ActionController@index');

Route::get('/self', function(){
    $user = Auth::user();
    return new SelfResource($user);
})->middleware('auth:api');

Route::delete('/oauth/token', function(Request $request){
    $request->user()->token()->revoke();
})->middleware('auth:api');

Route::get('/ping', function ()
{
    return "Test-API ping response";
})->middleware('auth:api');

Route::post('/verify_recaptcha', 'CaptchaController@verify');

//region users
Route::get('/users', 'UserController@index')->middleware('auth:api');
Route::post('/users', 'UserController@store');

Route::get('/users/{user_id}', 'UserController@show');
Route::patch('/users/{user_id}', 'UserController@update')->middleware('auth:api');
Route::delete('/users/{user_id}', 'UserController@destroy')->middleware('auth:api');

Route::put('/users/{user_id}/role', 'UserController@updateRole')->middleware('auth:api');
Route::get('/users/{user_id}/details', 'UserController@showDetails')->middleware('auth:api');

Route::get('/users/{user_id}/amendments', 'UserController@listAmendments');
Route::get('/users/{user_id}/subamendments', 'UserController@listSubAmendments');
Route::get('/users/{user_id}/comments', 'UserController@listComments');
Route::get('/users/{user_id}/discussions', 'UserController@listDiscussions');
Route::get('/users/{user_id}/statistics', 'UserController@showStatistics');

Route::get('/verify_user/{verification_token}', 'UserController@verifyUser');
//Route::get('/update_password/{verification_token}', 'UserController@updatePassword');
//endregion

//region discussions
Route::get('/discussions', 'DiscussionController@index');

Route::post('/discussions', 'DiscussionController@store')->middleware('auth:api');

//Route::post('/discussions', 'DiscussionController@test');

Route::get('/discussions/{discussion_id}', 'DiscussionController@show');

Route::patch('/discussions/{discussion_id}', 'DiscussionController@update')->middleware('auth:api');

Route::delete('/discussions/{discussion_id}', 'DiscussionController@destroy')->middleware('auth:api');

Route::get('/discussions/{discussion_id}/rating', 'DiscussionController@getRating');

Route::put('/discussions/{discussion_id}/rating', 'DiscussionController@createRating')->middleware('auth:api');

Route::get('/discussions/{discussion_id}/amendments', 'DiscussionController@listAmendments');

Route::post('/discussions/{discussion_id}/amendments', 'DiscussionController@createAmendment')->middleware('auth:api');

Route::get('/discussions/{discussion_id}/comments', 'DiscussionController@listComments');

Route::post('/discussions/{discussion_id}/comments', 'DiscussionController@createComment')->middleware('auth:api');

Route::get('/law_texts', 'DiscussionController@listLawTexts')->middleware('auth:api');

Route::get('/law_texts/{id}', 'DiscussionController@showLawText')->middleware('auth:api');

Route::get('/reload_law_texts', 'DiscussionController@reloadLawTexts')->middleware('auth:api');
//endregion

//region amendments
Route::get('/discussions/{discussion_id}/amendments/{id}', 'AmendmentController@show');
Route::delete('/discussions/{discussion_id}/amendments/{id}', 'AmendmentController@destroy')->middleware('auth:api');
Route::get('/discussions/{discussion_id}/amendments/{id}/ratings', 'AmendmentController@showRating');
Route::put('/discussions/{discussion_id}/amendments/{id}/ratings', 'AmendmentController@updateRating')->middleware('auth:api');
Route::get('/discussions/{discussion_id}/amendments/{id}/changes', 'AmendmentController@listChanges');
// Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/changes/{id}', 'AmendmentController@')
Route::get('/discussions/{discussion_id}/amendments/{id}/comments', 'AmendmentController@listComments');
Route::post('/discussions/{discussion_id}/amendments/{id}/comments', 'AmendmentController@createComment')->middleware('auth:api');
Route::get('/discussions/{discussion_id}/amendments/{id}/subamendments', 'AmendmentController@listSubAmendments');
Route::post('/discussions/{discussion_id}/amendments/{id}/subamendments', 'AmendmentController@createSubAmendment')->middleware('auth:api');
//endregion

//region sub_amendments
Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}', 'SubAmendmentController@show');
Route::patch('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}', 'SubAmendmentController@patch')->middleware('auth:api');
Route::delete('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}', 'SubAmendmentController@destroy')->middleware('auth:api');
Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}/rating', 'SubAmendmentController@showRating');
Route::put('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}/rating', 'SubAmendmentController@updateRating')->middleware('auth:api');
Route::get('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}/comments', 'SubAmendmentController@listComments');
Route::post('/discussions/{discussion_id}/amendments/{amendment_id}/subamendments/{id}/comments', 'SubAmendmentController@createComment')->middleware('auth:api');
//endregion

//region comments
Route::get('/comments', 'CommentController@index');

Route::get('/comments/{comment_id}', 'CommentController@show');

Route::delete('/comments/{comment_id}', 'CommentController@destroy')->middleware('auth:api');

Route::patch('/comments/{comment_id}', 'CommentController@update')->middleware('auth:api');

Route::get('/comments/{comment_id}/comments', 'CommentController@listComments');

Route::post('/comments/{comment_id}/comments', 'CommentController@createComment')->middleware('auth:api');

//Route::get('/comments/{comment_id}/ratings', 'CommentController@showRating');

Route::put('/comments/{comment_id}/user_rating', 'CommentController@updateRating')->middleware('auth:api');

Route::get('/comments/{comment_id}/reports', 'CommentController@listReports')->middleware('auth:api');

Route::post('tag_recommendations', 'CommentController@getTagsForText')->middleware('auth:api');
//endregion

//region reports
Route::get('/reports', 'ReportController@index')->middleware('auth:api');

Route::get('/reports/{report_id}', 'ReportController@show')->middleware('auth:api');

Route::post('/reports', 'ReportController@create')->middleware('auth:api');
//endregion

//region tags
Route::get('/tags', 'TagController@index');

Route::get('/tags/{tag_id}', 'TagController@show');
//endregion

//region actions
Route::get('/search', 'ActionController@searchArticles');

Route::get('/statistics', 'ActionController@getGeneralActivityStatistics')->middleware('auth:api');

Route::get('/statistics/user_activity', 'ActionController@getUserActivityStatistics')->middleware('auth:api');

Route::get('/statistics/ratings', 'ActionController@getRatingStatistics')->middleware('auth:api');

Route::get('/statistics/comment_ratings', 'ActionController@getCommentRatingStatistics')->middleware('auth:api');

Route::get('/statistics/object_activity', 'ActionController@getObjectActivityStatistics')->middleware('auth:api');

Route::get('/job_list', 'ActionController@getJobList');

Route::get('/graduation_list', 'ActionController@getGraduationList');

Route::get('/aspects', 'ActionController@getAspects');
//endregion

Route::get('/users/{user_id}/relevant', 'ActionController@getRelevantDiscussions')->middleware('auth:api');

//region Short URLs
Route::get('/amendments/{id}', 'AmendmentController@showShort');

Route::get('/subamendments/{id}', 'SubAmendmentController@showShort');
//endregion
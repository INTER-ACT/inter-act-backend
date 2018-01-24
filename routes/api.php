<?php

use Illuminate\Http\Request;

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

Route::get('/ping', function ()
{
    return "Test-API ping response";
})->middleware('auth:api');

//region users
Route::get('/users', 'UserController@index');
Route::post('/users', 'UserController@store');

Route::get('/users/{user_id}', 'UserController@show');
Route::patch('/users/{user_id}', 'UserController@update')->middleware('auth:api');
Route::delete('/users/{user_id}', 'UserController@destroy')->middleware('auth:api');

Route::put('/users/{user_id}/role', 'UserController@updateRole')->middleware('auth:api');

Route::get('/users/{user_id}/amendments', 'UserController@listAmendments');
Route::get('/users/{user_id}/subamendments', 'UserController@listSubAmendments');
Route::get('/users/{user_id}/comments', 'UserController@listComments');
Route::get('/users/{user_id}/discussions', 'UserController@listDiscussions');
Route::get('/users/{user_id}/statistics', 'UserController@showStatistics');

//endregion

//region discussions
Route::get('/discussions', 'DiscussionController@index');

Route::get('/discussions/{discussion_id}', 'DiscussionController@show');

Route::put('/discussions/{discussion_id}', 'DiscussionController@update');

Route::delete('/discussions/{discussion_id}', 'DiscussionController@destroy');

Route::get('/discussions/{discussion_id}/amendments', 'DiscussionController@listAmendments');

Route::post('/discussions/{discussion_id}/amendments', 'DiscussionController@createAmendment');

Route::get('/discussions/{discussion_id}/comments', 'DiscussionController@listComments');

Route::post('/discussions/{discussion_id}/comments', 'DiscussionController@createComment');

Route::get('/law_texts', 'DiscussionController@listLawTexts');
//endregion

//region amendments

//endregion

//region sub_amendments

//endregion

//region comments
Route::get('/comments', 'CommentController@index');

Route::get('/comments/{comment_id}', 'CommentController@show');

Route::delete('/comments/{comment_id}', 'CommentController@destroy');

Route::get('/comments/{comment_id}/comments', 'CommentController@listComments');

Route::get('/comments/{comment_id}/ratings', 'CommentController@showRating');

Route::get('/comments/{comment_id}/reports', 'CommentController@listReports');
//endregion

//region reports

//endregion

//region tags
Route::get('/tags', 'TagController@index');

Route::get('/tags/{tag_id}', 'TagController@show');
//endregion

//region actions
Route::get('/search', 'ActionController@searchArticles');

Route::get('/statistics', 'ActionController@getGeneralStatistics');

Route::get('/statistics/user_activity', 'ActionController@getUserActivityStatistics');

Route::get('/statistics/ratings', 'ActionController@getRatingStatistics');

Route::get('/statistics/comment_ratings', 'ActionController@getCommentRatingStatistics');

Route::get('/statistics/object_activity', 'ActionController@getObjectActivityStatistics');
//endregion

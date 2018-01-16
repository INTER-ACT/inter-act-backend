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

//region users

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

//endregion

//region reports

//endregion

//region actions

//endregion

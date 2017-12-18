<?php

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

Route::get('/', function ()
{
    return "Welcome to the Test-API!";
});

Route::get('/ping', function ()
{
    return "Test-API ping response";
});

Route::get('/users', function(){
   return User::all();
});

Route::get('/users/{user_id}', function($user_id){
   return User::with(['role', 'discussions', 'amendments', 'sub_amendments', 'comments', 'rated_comments', 'reports'])->where('id', $user_id)->get();
});

Route::get('/users/{user_id}/discussions', function($user_id){
    return User::find($user_id)->discussions;
});

//TODO: change subressources of users to primary-resources? the user can be gotten from the request for example
Route::get('/users/{user_id}/discussions/{discussion_id}', function(int $discussion_id){
   return Discussion::with(['user', 'amendments', 'comments', 'tags'])->where('id', $discussion_id)->get();
});

Route::get('/users/{user_id}/discussions/{discussion_id}/amendments', function(int $discussion_id){
    return Discussion::find($discussion_id)->amendments;
});

Route::get('/users/{user_id}/discussions/{discussion_id}/amendments/{amendment_id}', function(int $amendment_id){
    return Amendment::with(['user', 'discussion', 'sub_amendments', 'comments', 'tags', 'ratings', 'rating_aspects', 'reports'])->where('id', $amendment_id)->get();
});

Route::get('/users/{user_id}/discussions/{discussion_id}/amendments/{amendment_id}/subamendments', function(int $amendment_id){
    return Amendment::find($amendment_id)->sub_amendments;
});

Route::get('/subamendments/{subamendment_id}', function(int $subamendment_id){
    return SubAmendment::with(['user', 'amendment', 'comments', 'tags', 'ratings', 'rating_aspects', 'reports'])->where('id', $subamendment_id)->get();
});

Route::get('/subamendments/{subamendment_id}/comments', function(int $subamendment_id){
    return SubAmendment::find($subamendment_id)->comments;
});

Route::get('/users/{user_id}/discussions/{discussion_id}/comments', function(int $discussion_id){
    return Discussion::find($discussion_id)->comments;
});

Route::get('/comments', function(){
    return Comment::with('parent')->get();
});

Route::get('/comments/{comment_id}', function(int $comment_id){
    return Comment::with(['user', 'parent', 'comments', 'tags', 'reports', 'rating_users'])->where('id', $comment_id)->get();
});

Route::get('/reports', function(){
    return Report::all();
});


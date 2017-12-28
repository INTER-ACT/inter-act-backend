<?php

use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CommentResources\CommentResource;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\DiscussionResources\DiscussionResource;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\PostResources\TagResource;
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
    return new CommentResource(Comment::find($comment_id)); //TODO: optimize performance by eager loading in routes already
    //Comment::with(['user', 'parent', 'comments', 'tags', 'reports', 'rating_users'])->where('id', $comment_id)->get()
});

Route::get('/tags', function(){
    return new TagCollection(Tag::all());
});

Route::get('/tags/{tag_id}', function(int $tag_id){
    return new TagResource(Tag::find($tag_id));
});

Route::get('/reports', function(){
    return Report::all();
});


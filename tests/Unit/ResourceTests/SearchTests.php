<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class SearchTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testSearchResource()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion_count = 2;
        $amendment_count = 2;
        $subamendment_count = 2;
        $comment_count = 2;
        $discussions = factory(Discussion::class, $discussion_count)->create(['user_id' => $user->id, 'title' => 'asdf']);
        $amendments = factory(Amendment::class, $amendment_count)->create(['user_id' => $user->id, 'discussion_id' => $discussions[0]->id, 'updated_text' => 'asdf']);
        $subamendments = factory(SubAmendment::class, $subamendment_count)->create(['user_id' => $user->id, 'amendment_id' => $amendments[0]->id, 'updated_text' => 'asdf']);
        $comments = factory(Comment::class, $comment_count)->create(['user_id' => $user->id, 'commentable_id' => $subamendments[0]->id, 'commentable_type' => $subamendments[0]->getType(), 'content' => 'asdf']);

        $resourcePath = $this->baseURI . '/search?search_term=asdf';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'discussions' => [
                'href' => $resourcePath,
                'total' => 2,
                'discussions' => $discussions->transform(function ($discussion){
                    return [
                        'href' => url($discussion->getResourcePath()),
                        'id' => $discussion->id,
                        'title' => $discussion->title
                    ];})->toArray()
            ],
            'amendments' => $amendments->toArray(),
            'subamendments' => $subamendments->toArray(),
            'comments' => [
                'href' => $resourcePath,
                //'total' => 2,
                'comments' => $comments->transform(function ($comment){
                    return [
                        'href' => url($comment->getResourcePath()),
                        'id' => $comment->id
                    ];})->toArray()
            ]
        ]);
    }
}

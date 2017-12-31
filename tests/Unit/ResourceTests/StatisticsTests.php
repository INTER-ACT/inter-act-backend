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

class StatisticsTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testUserStatisticsResource()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion_count = 3;
        $amendment_count = 100;
        $subamendment_count = 1;
        $comment_count = 0;
        $discussions = factory(Discussion::class, $discussion_count)->create(['user_id' => $user->id]);
        $amendments = factory(Amendment::class, $amendment_count)->create(['user_id' => $user->id, 'discussion_id' => $discussions[0]->id]);
        $subamendments = factory(SubAmendment::class, $subamendment_count)->create(['user_id' => $user->id, 'amendment_id' => $amendments[0]->id]);
        $comments = factory(Comment::class, $comment_count)->create(['user_id' => $user->id, 'commentable_id' => $subamendments[0]->id, 'commentable_type' => $subamendments[0]->getType()]);

        $resourcePath = $this->baseURI . $user->getResourcePath() . '/statistics';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'comments_count' => $comment_count,
            'amendments_count' => $amendment_count,
            'subamendments_count' => $subamendment_count
        ]);
    }

    /** @test */
    public function testStatisticsResource()
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

        $resourcePath = $this->baseURI . '/statistics';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath
        ]);
    }

    /** @test */
    public function testDiscussionStatisticsResource()
    {
        self::assertEquals(true, true);
        //TODO: implement testDiscussionStatisticsResource() if needed
    }
}

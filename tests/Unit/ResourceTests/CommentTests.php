<?php

namespace Tests\Unit;

use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Tags\Tag;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class CommentTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testCommentResource()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $parent = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);
        $tag1 = Tag::getNutzungFremderInhalte();
        $tag2 = Tag::getSozialeMedien();
        $comment = factory(Comment::class)->create([
            'content' => 'TestTitle',
            'sentiment' => 1,
            'user_id' => $user->id,
            'commentable_id' => $parent->getId(),
            'commentable_type' => get_class($parent)
        ]);

        //tags
        $comment->tags()->attach([$tag1->id, $tag2->id]);

        //ratings --> sum = 2
        $comment->rating_users()->attach($user->id, ['rating_score' => 1]);
        $comment->rating_users()->attach(factory(User::class)->create()->id, ['rating_score' => 1]);
        $comment->rating_users()->attach(factory(User::class)->create()->id, ['rating_score' => -1]);
        $comment->rating_users()->attach(factory(User::class)->create()->id, ['rating_score' => 1]);

        $resourcePath = $this->getUrl($comment->getResourcePath());
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->toAtomString(),

            'author' => [
                'href' => $this->getUrl($comment->user->getResourcePath()),
                'id' => $comment->user->id
            ],
            'tags' => [
                [
                    'id' => $tag1->id,
                    'name' => $tag1->name,
                    'description' => $tag1->description
                ],
                [
                    'id' => $tag2->id,
                    'name' => $tag2->name,
                    'description' => $tag2->description
                ]
            ],
            'comments' => ['href' => $resourcePath . '/comments'],
            'parent' => [
                'href' => $this->getUrl($parent->getResourcePath()),
                'id' => $parent->id
            ],
            'positive_ratings' => 3,
            'negative_ratings' => 1,
            'user_rating' => 1
        ]);
    }

    /** @test */
    public function testCommentCollection()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion1 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion2 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $comment1 = factory(Comment::class)->create([
            'content' => 'TestTitle',
            'sentiment' => 1,
            'user_id' => $user->id,
            'commentable_id' => $discussion1->getId(),
            'commentable_type' => get_class($discussion1)
        ]);
        $comment2 = factory(Comment::class)->create([
            'content' => 'TestTitle',
            'sentiment' => 1,
            'user_id' => $user->id,
            'commentable_id' => $discussion2->getId(),
            'commentable_type' => get_class($discussion2)
        ]);

        $resourcePath = $this->getUrl('/comments');
        $response = $this->get($resourcePath);
        $response->assertJson([
            'data'=> [
                'href' => $resourcePath,
                'comments' => [
                    [
                        'href' => $this->getUrl($comment1->getResourcePath()),
                        'id' => $comment1->id
                    ],
                    [
                        'href' => $this->getUrl($comment2->getResourcePath()),
                        'id' => $comment2->id
                    ]
                ]
            ]
        ]);
    }
}

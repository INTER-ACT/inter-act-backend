<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.12.17
 * Time: 09:53
 */

namespace Tests\Unit;


use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Role;
use App\Tags\Tag;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ResourceJsonTests extends TestCase
{
    use WithoutMiddleware;
    use DatabaseMigrations;

    protected $baseURI = 'http://localhost';

    /** @test */
    public function testDiscussionResource()
    {
        $this->be(factory(User::class)->create());
        $discussion = factory(Discussion::class)->create([
            'title' => 'TestTitle',
            'law_text' => 'TestLawText',
            'law_explanation' => 'TestLawExplanation',
            'user_id' => \Auth::id()
        ]);
        $resourcePath = $this->baseURI . $discussion->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $discussion->id,
            'title' => $discussion->title,
            'created_at' => $discussion->created_at->toIso8601String(),
            'updated_at' => $discussion->updated_at->toIso8601String(),
            'law_text' => $discussion->law_text,
            'law_explanation' => $discussion->law_explanation,
            'author' => [
                'href' => $this->baseURI . $discussion->user->getResourcePath(),
                'id' => $discussion->user->id
            ],
            'amendments' => ['href' => $resourcePath . '/amendments'],
            'comments' => ['href' => $resourcePath . '/comments']
        ]);
    }

    /** @test */
    public function testDiscussionCollection()
    {
        $this->be(factory(User::class)->create());
        $discussion1 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion2 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $resourcePath = $this->baseURI . '/discussions';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'total' => 2,
            'discussions' => [
                [
                    'href' => $this->baseURI . $discussion1->getResourcePath(),
                    'id' => $discussion1->id,
                    'title' => $discussion1->title
                    //TODO: article?????
                ],
                [
                    'href' => $this->baseURI . $discussion2->getResourcePath(),
                    'id' => $discussion2->id,
                    'title' => $discussion2->title
                ]
            ]
        ]);
    }

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
            'commentable_id' => $parent->getIdProperty(),
            'commentable_type' => get_class($parent)
        ]);

        //tags
        $comment->tags()->attach([$tag1->id, $tag2->id]);

        //ratings --> sum = 2
        $comment->rating_users()->attach($user->id, ['rating_score' => 1]);
        $comment->rating_users()->attach(factory(User::class)->create()->id, ['rating_score' => 1]);
        $comment->rating_users()->attach(factory(User::class)->create()->id, ['rating_score' => -1]);
        $comment->rating_users()->attach(factory(User::class)->create()->id, ['rating_score' => 1]);

        $resourcePath = $this->baseURI . $comment->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->toIso8601String(),

            'author' => [
                'href' => $this->baseURI . $comment->user->getResourcePath(),
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
                'href' => $this->baseURI . $parent->getResourcePath(),
                'id' => $parent->id
            ],
            'rating' => 2
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
            'commentable_id' => $discussion1->getIdProperty(),
            'commentable_type' => get_class($discussion1)
        ]);
        $comment2 = factory(Comment::class)->create([
            'content' => 'TestTitle',
            'sentiment' => 1,
            'user_id' => $user->id,
            'commentable_id' => $discussion2->getIdProperty(),
            'commentable_type' => get_class($discussion2)
        ]);

        $resourcePath = $this->baseURI . '/comments';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'comments' => [
                [
                    'href' => $this->baseURI . $comment1->getResourcePath(),
                    'id' => $comment1->id
                ],
                [
                    'href' => $this->baseURI . $comment2->getResourcePath(),
                    'id' => $comment2->id
                ]
            ]
        ]);
    }
}
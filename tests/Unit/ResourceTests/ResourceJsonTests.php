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
        $resourcePath = 'test/discussions/' . $discussion->id;
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
                'href' => '/users/' . $discussion->user->id,
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
        $resourcePath = 'test/discussions';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'data'=>[
                    'href' => $resourcePath,
                    'total' => 2,
                    'discussions' => [
                        [
                            'href' => $resourcePath . '/' . $discussion1->id,
                            'id' => $discussion1->id,
                            'title' => $discussion1->title
                            //TODO: article?????
                        ],
                        [
                            'href' => $resourcePath . '/' . $discussion2->id,
                            'id' => $discussion2->id,
                            'title' => $discussion2->title
                        ]
                    ]
                ]
        ]);
    }

    /** @test */
    public function testCommentResource()
    {
        $this->be(factory(User::class)->create());
        $parent = factory(Discussion::class)->create([
            'user_id' => \Auth::id()
        ]);
        $tag1 = Tag::getNutzungFremderInhalte();
        $tag2 = Tag::getSozialeMedien();
        $comment = factory(Comment::class)->create([
            'content' => 'TestTitle',
            'sentiment' => 1,
            'user_id' => \Auth::id(),
            'commentable_id' => $parent->getIdProperty(),
            'commentable_type' => get_class($parent)
        ]);
        $comment->tags()->attach([$tag1->id, $tag2->id]);

        $resourcePath = 'test/comments/' . $comment->id;
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->toIso8601String(),

            'author' => [
                'href' => '/users/' . $comment->user->id,
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
                'href' => 'test/discussions/' . $parent->id,
                'id' => $parent->id
            ]
        ]);
    }

    /** @test */
    public function testCommentCollection()
    {
        $this->be(factory(User::class)->create());
        $discussion1 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion2 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $resourcePath = 'test/discussions';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'data'=>[
                'href' => $resourcePath,
                'total' => 2,
                'discussions' => [
                    [
                        'href' => $resourcePath . '/' . $discussion1->id,
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                        //TODO: article?????
                    ],
                    [
                        'href' => $resourcePath . '/' . $discussion2->id,
                        'id' => $discussion2->id,
                        'title' => $discussion2->title
                    ]
                ]
            ]
        ]);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.12.17
 * Time: 09:53
 */

namespace Tests\Unit;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Reports\Report;
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

    //region Discussions
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
    //endregion

    //region Comments
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
    //endregion

    //region reports
    /** @test */
    public function testReportResource()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id
        ]);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $amendment->getIdProperty(),
            'reportable_type' => get_class($amendment),
            'explanation' => 'Test Description'
        ]);

        $resourcePath = $this->baseURI . $report->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $report->id,

            'user' => [
                'href' => $this->baseURI . $report->user->getResourcePath(),
                'id' => $report->user->id
            ],
            'reported_item' => [
                'href' => $this->baseURI . $report->reportable->getResourcePath(),
                'id' => $report->reportable->id,
                'type' => get_class($report->reportable)
            ],

            'description' => $report->explanation
        ]);
    }

    /** @test */
    public function testReportCollection()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id
        ]);
        $subamendment = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendment->id
        ]);
        $report1 = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $amendment->getIdProperty(),
            'reportable_type' => get_class($amendment),
            'explanation' => 'Test Description 1'
        ]);
        $report2 = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $subamendment->getIdProperty(),
            'reportable_type' => $subamendment->getType(),
            'explanation' => 'Test Description 2'
        ]);

        $resourcePath = $this->baseURI . '/reports';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'reports' => [
                [
                    'href' => $this->baseURI . $report1->getResourcePath(),
                    'id' => $report1->id
                ],
                [
                    'href' => $this->baseURI . $report2->getResourcePath(),
                    'id' => $report2->id
                ]
            ]
        ]);
    }
    //endregion

    //region Tags
    /** @test */
    public function testTagResource()
    {
        $tag = Tag::getNutzungFremderInhalte();

        $resourcePath = $this->baseURI . $tag->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $tag->id,
            'name' => $tag->name,
            'description' => $tag->description
        ]);
    }

    /** @test */
    public function testTagCollection()
    {
        $tag1 = Tag::getNutzungFremderInhalte();
        $tag2 = Tag::getSozialeMedien();

        $resourcePath = $this->baseURI . '/tags';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'tags' => [
                [
                    //'href' => $this->baseURI . $tag1->getResourcePath(),
                    'id' => $tag1->id,
                    'name' => $tag1->name,
                    'description' => $tag1->description
                ],
                [
                    //'href' => $this->baseURI . $tag2->getResourcePath(),
                    'id' => $tag2->id,
                    'name' => $tag2->name,
                    'description' => $tag2->description
                ]
            ]
        ]);
    }
    //endregion
}
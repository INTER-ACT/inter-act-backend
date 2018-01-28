<?php

namespace Tests\Feature;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Reports\Report;
use App\Tags\Tag;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use phpDocumentor\Reflection\Types\Null_;
use Tests\TestCase;

class AmendmentTests extends TestCase
{
    use DatabaseMigrations;

    public function testGettingAmendments()
    {
        $discussion = factory(Discussion::class)->states('user')->create();
        $amendments = factory(Amendment::class, 5)->states('user')->create([
            'discussion_id' => $discussion->id
        ]);

        $url = "/discussions/$discussion->id/amendments";

        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function testGettingAmendment()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id";

        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function testDeletingAmendment()
    {
        $admin = factory(User::class)->states('admin')->create();
        $amendment = factory(Amendment::class)->states('discussion')->create([
            'user_id' => $admin->id
        ]);

        Passport::actingAs($admin);

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id";

        $response = $this->delete($url);

        $response->assertStatus(204);

        $deletedAmendment = Amendment::find($amendment->id);
        self::assertNull($deletedAmendment);
    }

    public function testDeletingAmendmentWithoutAuthentication()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id";

        $response = $this->delete($url);

        $response->assertStatus(401);
        self::assertNotNull(Amendment::find($amendment->id));
    }

    /**
     * Tests that an unprivileged (normal) User cannot delete an Amendment
     */
    public function testDeletingAmendmentAsUser()
    {
        $user = factory(User::class)->create();
        $amendment = factory(Amendment::class)->states('discussion')->create([
            'user_id' => $user->id
        ]);

        Passport::actingAs($user);

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id";

        $response = $this->delete($url);

        $response->assertStatus(403);
        self::assertNotNull(Amendment::find($amendment->id));
    }

    public function testGettingSubAmendments()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $subamendments = factory(SubAmendment::class, 5)->states('user')->create([
            'amendment_id' => $amendment->id
        ]);

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/subamendments";

        $response = $this->get($url);
        $response->assertStatus(200);
    }

    public function testGettingChanges()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $subamendments = factory(SubAmendment::class, 2)->states('user', 'accepted')->create([
            'amendment_id' => $amendment->id
        ]);

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/changes";
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    public function testCreatingSubAmendment()
    {
        $user = factory(User::class)->create();

        $tagA = Tag::getBildungUndWissenschaft();
        $tagB =  Tag::getRespektUndAnerkennung();

        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $subamendment = factory(SubAmendment::class)->make();

        Passport::actingAs($user);

        $data = [
            'updated_text' => $subamendment->updated_text,
            'explanation' => $subamendment->explanation,
            'tags' => [$tagA->id, $tagB->id]
        ];

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/subamendments";

        $response = $this->post($url, $data);

        $response->assertStatus(201);

        $responseData = $response->decodeResponseJson();
        $subamendmentUrl = url($url . '/' . $responseData['id']);

        self::assertEquals($subamendmentUrl, $responseData['href']);

        $createdSubAmendment = SubAmendment::find($responseData['id']);
        self::assertNotNull($createdSubAmendment);

        self::assertEquals($subamendment->updated_text, $createdSubAmendment->updated_text);
        self::assertEquals($subamendment->explanation, $createdSubAmendment->explanation);
        self::assertEquals($user->id, $createdSubAmendment->user_id);
        self::assertEquals($amendment->id, $createdSubAmendment->amendment_id);
        self::assertEquals(2, count($createdSubAmendment->tags));
    }

    public function testCreatingSubAmendmentWithoutAuthentication()
    {
        $user = factory(User::class)->create();

        $tagA = Tag::getBildungUndWissenschaft();
        $tagB =  Tag::getRespektUndAnerkennung();

        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $subamendment = factory(SubAmendment::class)->make();

        $data = [
            'updated_text' => $subamendment->updated_text,
            'explanation' => $subamendment->explanation,
            'tags' => [$tagA->id, $tagB->id]
        ];

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/subamendments";

        $response = $this->post($url, $data);

        $response->assertStatus(401);
    }

    public function testGettingComments()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $comments = factory(Comment::class)->states('user')->create([
            'commentable_type' => Amendment::class,
            'commentable_id' => $amendment->id
        ]);

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/comments";

        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function testCreatingComment()
    {
        $user = factory(User::class)->create();

        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $comment = factory(Comment::class)->make();

        $data = [
            'comment_text' => $comment->content,
            'tags' => [
                Tag::getRespektUndAnerkennung()->id,
                Tag::getFreiheitenDerNutzer()->id
            ]
        ];

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/comments";

        Passport::actingAs($user);
        $response = $this->post($url, $data);

        $response->assertStatus(201);

        $responseData = $response->decodeResponseJson();
        self::assertNotNull($responseData['id']);

        $href = url('/comments/' . $responseData['id']);
        self::assertEquals($href, $responseData['href']);

        $createdComment = Comment::find($responseData['id']);
        self::assertNotNull($createdComment);

        self::assertEquals($comment->comment_text, $createdComment->comment_text);
        self::assertEquals(2, count($createdComment->tags));
    }

    public function testCreatingCommentWithoutAuthentication()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $comment = factory(Comment::class)->make();

        $data = [
            'comment_text' => $comment->content,
            'tags' => [
                Tag::getRespektUndAnerkennung()->id,
                Tag::getFreiheitenDerNutzer()->id
            ]
        ];

        $url = "/discussions/$amendment->discussion_id/amendments/$amendment->id/comments";

        $response = $this->post($url, $data);

        $response->assertStatus(401);
    }


    public function testCreatingReport()
    {
        // TODO
        self::assertTrue(true);
    }

}
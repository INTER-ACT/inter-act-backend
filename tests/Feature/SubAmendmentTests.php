<?php

namespace Tests\Feature;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Domain\Manipulators\SubAmendmentManipulator;
use App\Http\Resources\SubAmendmentResources\SubAmendmentResource;
use App\Tags\Tag;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SubAmendmentTests extends TestCase
{
    use DatabaseMigrations;

    //region GET /subamendments/{id}
    public function testGettingSubAmendment()
    {
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create();

        $response = $this->get($subamendment->getResourcePath());

        $response->assertStatus(200);
    }
    //endregion

    //region PATCH /subamendments/{id}
    public function testAcceptingSubAmendment()
    {
        $user = factory(User::class)->create();
        $amendment = factory(Amendment::class)->states('discussion')->create([
            'user_id' => $user->id
        ]);
        $subamendment = factory(SubAmendment::class)->states('user')->create([
            'amendment_id' => $amendment->id
        ]);

        Passport::actingAs($user);

        $data = [
            'accept' => true
        ];

        $response = $this->patch($subamendment->getResourcePath(), $data);

        $response->assertStatus(204);

        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertEquals(SubAmendment::ACCEPTED_STATUS, $newSubamendment->status);
        self::assertNotNull($newSubamendment->handled_at);
    }

    /**
     * Tests that a SubAmendment cannot be accepted by an unauthenticated user
     */
    public function testAcceptingSubAmendmentWithoutAuthentication()
    {
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create()->fresh();

        $data = [
            'accept' => true
        ];

        $response = $this->patch($subamendment->getResourcePath(), $data);

        $response->assertStatus(401);

        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertEquals($subamendment->status, $newSubamendment->status);
    }

    /**
     * Tests that a SubAmendment cannot be accepted by a User who does not own the Amendment
     */
    public function testAcceptingSubAmendmentAsDifferentUser()
    {
        $otherUser = factory(User::class)->create();
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create()->fresh();

        Passport::actingAs($otherUser);

        $data = [
            'accept' => true
        ];

        $response = $this->patch($subamendment->getResourcePath(), $data);

        $response->assertStatus(403);

        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertEquals($subamendment->status, $newSubamendment->status);
    }

    /**
     * Tests that a SubAmendment can be rejected if the User does everything correctly
     */
    public function testRejectingSubAmendment()
    {
        $user = factory(User::class)->create();
        $amendment = factory(Amendment::class)->states('discussion')->create([
            'user_id' => $user->id
        ]);
        $subamendment = factory(SubAmendment::class)->states('user')->create([
            'amendment_id' => $amendment->id
        ]);

        Passport::actingAs($user);

        $data = [
            'accept' => false,
            'explanation' => 'Some Explanation'
        ];

        $response = $this->patch($subamendment->getResourcePath(), $data);

        $response->assertStatus(204);
        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertEquals(SubAmendment::REJECTED_STATUS, $newSubamendment->status);
        self::assertNotNull($newSubamendment->handled_at);
        self::assertEquals($data['explanation'], $newSubamendment->handle_explanation);
    }

    /**
     * Tests that a User who isn't authenticated cannot reject a SubAmendment
     */
    public function testRejectingSubAmendmentWithoutAuthentication()
    {
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create()->fresh();

        $data = [
            'accept' => false,
            'explanation' => 'Some Explanation'
        ];

        $response = $this->patch($subamendment->getResourcePath(), $data);

        $response->assertStatus(401);

        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertEquals($subamendment->status, $newSubamendment->status);
    }

    /**
     * Tests that a User who doesn't own the Amendment cannot reject SubAmendments
     */
    public function testRejectingSubAmendmentAsDifferentUser()
    {
        $otherUser = factory(User::class)->create();
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create()->fresh();

        Passport::actingAs($otherUser);

        $data = [
            'accept' => false,
            'explanation' => 'Some Explanation'
        ];

        $response = $this->patch($subamendment->getResourcePath(), $data);

        $response->assertStatus(403);

        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertEquals($subamendment->status, $newSubamendment->status);
    }
    //endregion

    //region DELETE /subamendments/{id}
    public function testDeletingSubAmendment()
    {
        $admin = factory(User::class)->states('admin')->create();
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create();

        Passport::actingAs($admin);

        $response = $this->delete($subamendment->getResourcePath());

        $response->assertStatus(204);

        $newSubamendment = SubAmendment::find($subamendment->id);
        self::assertNull($newSubamendment);
    }
    //endregion

    public function testUpdatingRating()
    {
        // TODO
        self::assertTrue(true);
    }
    //endregion

    public function testGettingRating()
    {
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();

        $response = $this->get($subamendment->getResourcePath());

        $response->assertStatus(200);
    }

    public function testGettingComments()
    {
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . '/comments';
        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function testCreatingComment()
    {
        $user = factory(User::class)->create();
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();

        $comment = factory(Comment::class)->make();
        $tags = [Tag::getFreiheitenDerNutzer()->id, Tag::getKulturellesErbe()->id];

        Passport::actingAs($user);

        $data = [
            'comment_text' => $comment->content,
            'tags' => $tags
        ];
        $url = $subamendment->getResourcePath() . '/comments';
        $response = $this->post($url, $data);

        $response->assertStatus(201);

        $responseData = $response->decodeResponseJson();

        $newComment = Comment::find($responseData['id']);
        self::assertNotNull($newComment);
        self::assertEquals($comment->content, $newComment->content);
        // TODO test tags
    }

    /**
     * Tests that a Comment cannot be created by User who is not authenticated
     */
    public function testCreatingCommentWithoutAuthentication()
    {
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();

        $comment = factory(Comment::class)->make();
        $tags = [Tag::getFreiheitenDerNutzer()->id, Tag::getKulturellesErbe()->id];

        $data = [
            'comment_text' => $comment->content,
            'tags' => $tags
        ];
        $url = $subamendment->getResourcePath() . '/comments';
        $response = $this->post($url, $data);

        $response->assertStatus(401);
    }
}
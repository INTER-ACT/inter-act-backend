<?php

namespace Tests\Feature;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\MultiAspectRating;
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
    /**
     * Tests that a SubAmendment can be accepted
     */
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
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create();
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $url = $subamendment->getResourcePath() . '/rating';
        $data = [
            MultiAspectRating::ASPECT1 => true,
            MultiAspectRating::ASPECT2 => true,
            MultiAspectRating::ASPECT3 => true,
            MultiAspectRating::ASPECT4 => true,
            MultiAspectRating::ASPECT5 => true,
            MultiAspectRating::ASPECT6 => true,
            MultiAspectRating::ASPECT7 => true,
            MultiAspectRating::ASPECT8 => true,
            MultiAspectRating::ASPECT9 => true,
            MultiAspectRating::ASPECT10 => true,
        ];

        $response = $this->put($url, $data);

        $response->assertStatus(204);

    }

    /**
     * Tests that if a user is rating a SubAmendment twice, the old rating is deleted
     *  - create rating
     *  - create 2nd rating
     *  - check total ratings
     */
    public function testUpdatingRatingTwice()
    {
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create();
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $url = $subamendment->getResourcePath() . '/rating';
        $data = [
            MultiAspectRating::ASPECT1 => true,
            MultiAspectRating::ASPECT2 => true,
            MultiAspectRating::ASPECT3 => true,
            MultiAspectRating::ASPECT4 => true,
            MultiAspectRating::ASPECT5 => true,
            MultiAspectRating::ASPECT6 => true,
            MultiAspectRating::ASPECT7 => true,
            MultiAspectRating::ASPECT8 => true,
            MultiAspectRating::ASPECT9 => true,
            MultiAspectRating::ASPECT10 => true,
        ];

        $response = $this->put($url, $data);

        $response->assertStatus(204);

        $data = [
            MultiAspectRating::ASPECT1 => true,
            MultiAspectRating::ASPECT2 => true,
            MultiAspectRating::ASPECT3 => true,
            MultiAspectRating::ASPECT4 => false,
            MultiAspectRating::ASPECT5 => true,
            MultiAspectRating::ASPECT6 => true,
            MultiAspectRating::ASPECT7 => false,
            MultiAspectRating::ASPECT8 => true,
            MultiAspectRating::ASPECT9 => true,
            MultiAspectRating::ASPECT10 => true,
        ];

        $response = $this->put($url, $data);

        $response->assertStatus(204);

        $response = $this->get($url);
        $responseData = $response->decodeResponseJson();

        self::assertEquals(true, $responseData['user_rating'][MultiAspectRating::ASPECT1]);
        self::assertEquals(1, $responseData['total_rating'][MultiAspectRating::ASPECT1]);

    }

    /**
     * Tests getting a Rating, before anyone has rated the SubAmendment
     */
    public function testGettingEmptyRating()
    {
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();

        $url = $subamendment->getResourcePath() . '/rating';

        $response = $this->get($url);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        foreach($data['total_rating'] as $rating)
            self::assertEquals(0, $rating);
    }

    /**
     * Tests getting a Rating, with multiple Users rating the subamendment
     * - create rating from multiple users
     * - check total counts
     */
    public function testGettingRating()
    {
        // could be extended to testing all aspects
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();

        $url = $subamendment->getResourcePath() . '/rating';

        for($i = 0; $i < 5; $i++)
        {
            $data = factory(MultiAspectRating::class)->raw([
                MultiAspectRating::ASPECT1 => true              // TODO check all values
            ]);
            $user = factory(User::class)->create();

            Passport::actingAs($user);
            $response = $this->put($url, $data);
            $response->assertStatus(204);
        }

        $response = $this->get($url);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        self::assertEquals(5, $data['total_rating'][MultiAspectRating::ASPECT1] );
    }

    /**
     * Tests that requesting an empty list of comments works
     */
    public function testGettingEmptyCommentsList()
    {
        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();

        $url = $subamendment->getResourcePath() . '/comments';
        $response = $this->get($url);

        $response->assertStatus(200);
    }

    /**
     * Tests that requesting a list of comments works
     */
    public function testGettingComments()
    {
        $commentCount = 5;

        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class, $commentCount)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . '/comments';
        $response = $this->get($url);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        self::assertEquals($commentCount, count($data['data']['comments']));
    }

    /**
     * Tests that max 100 comments are returned, if no pagination is provided
     */
    public function testGettingMaxComments()
    {
        $commentCount = 150;
        $maxCommentsPerPage = 100;

        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class, $commentCount)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . '/comments';
        $response = $this->get($url);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        self::assertEquals($maxCommentsPerPage, count($data['data']['comments']));
    }

    /**
     * Tests that not more than 100 comments can be requested with pagination params
     */
    public function testGettingMoreThanMaxComments()
    {
        $commentCount = 150;

        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class, $commentCount)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . "/comments?start=0&count=120";
        $response = $this->get($url);

        $response->assertStatus(413);
    }

    /**
     * Tests that a certain number of comments can be retrieved with pagination
     */
    public function testGettingCommentsWithPagination()
    {
        $commentCount = 150;
        $perPage = 15;

        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class, $commentCount)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . "/comments?start=0&count=$perPage";
        $response = $this->get($url);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        self::assertEquals($perPage, count($data['data']['comments']));
    }

    /**
     * Tests that the second page of comments can be retrieved
     */
    public function testGettingSecondPageWithPagination()
    {
        $commentCount = 20;
        $perPage = 15;

        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class, $commentCount)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . "/comments?start=2&count=$perPage";
        $response = $this->get($url);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        $expectedCount = $commentCount - $perPage;

        self::assertEquals($expectedCount, count($data['data']['comments']));
    }

    /**
     * Tests that the meta data provided by the pagination is correct
     */
    public function testCommentPaginationMetaData()
    {
        $commentCount = 150;
        $perPage = 15;

        $subamendment = factory(Subamendment::class)->states('user', 'amendment')->create();
        $comments = factory(Comment::class, $commentCount)->states('user')->create([
            'commentable_type' => SubAmendment::class,
            'commentable_id' => $subamendment->id
        ]);

        $url = $subamendment->getResourcePath() . "/comments?start=1&count=$perPage";
        $response = $this->get($url);

        $response->assertJson([
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'to' => 15,
                'last_page' => 10,
                'path' => 'http://localhost/discussions/1/amendments/1/subamendments/1/comments',
                'per_page' => 15,
                'total' => 150,
            ],
            'links' => [
                'first' => 'http://localhost/discussions/1/amendments/1/subamendments/1/comments?start=1',
                'last' => 'http://localhost/discussions/1/amendments/1/subamendments/1/comments?start=10',
                'prev' => Null,
                'next' => 'http://localhost/discussions/1/amendments/1/subamendments/1/comments?start=2'
            ]
        ]);
    }

    /**
     * Tests that a comment can be created with valid inputs
     */
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
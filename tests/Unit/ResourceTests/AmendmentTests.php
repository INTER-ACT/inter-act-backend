<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Tags\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class AmendmentTests extends TestCase
{
    use ResourceTestTrait;

    /**
     * Test the Amendment Resource
     * With an Amendment, that does not contain tags, subamendments or comments
     *
     * @test */
    public function testAmendmentResourceWithEmptyLists()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();

        $amendmentVersion = 1;          // (no subamendments have been accepted)

        $resourcePath = $this->baseURI . $amendment->getResourcePath();
        $subamendmentsPath = $resourcePath . "/subamendments";
        $commentsPath = $resourcePath ."/comments";

        $response = $this->get($resourcePath);

        $ratingsUri = $resourcePath. "/ratings";
        $userRating = $resourcePath . "/user_rating";


        $response->assertJson([
            'href' => $resourcePath,
            'id' => $amendment->id,
            'explanation' => $amendment->explanation,
            'changes' => [],
            'version' => $amendmentVersion,
            'ratings' => [
                'href' => $ratingsUri
            ],
            'user_rating' => [
                'href' => $userRating
            ],
            'tags'=> [],
            'author' => [
                'href' => $amendment->user->getResourcePath(),
                'id' => $amendment->user->id
            ],
            'subamendments' => [
                'href' => $subamendmentsPath
            ],
            'comments' => [
                'href' => $commentsPath
            ],
            'created_at' => $amendment->created_at->toIso8601String(),
            'updated_at' => $amendment->updated_at->toIso8601String()
        ]);
    }


    /**
     * Test a fully filled Amendment Resource
     * Containing tags, subamendments and comments
     *
     * @test */
    public function testFullAmendmentResource()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();

        $subamendments = factory(SubAmendment::class, 10)->states('user')->create([
            'amendment_id' => $amendment->id
        ]);
        $comments = factory(Comment::class, 3)->states('user')->create([
            'commentable_id' => $amendment->id,
            'commentable_type' => Amendment::class
        ]);
        $tags = [
            Tag::getNutzungFremderInhalte(),
            Tag::getSozialeMedien()
        ];


        $amendment->sub_amendments = $subamendments;
        $amendment->comments = $comments;
        $amendment->tags()->attach([$tags[0]->id, $tags[1]->id]);

        $amendmentVersion = 1; // TODO update after version has been addded to the amendment model

        $resourcePath = $this->baseURI . $amendment->getResourcePath();
        $commentsPath = $resourcePath ."/comments";
        $subamendmentsPath = $resourcePath . "/subamendments";
        $ratingsUri = $resourcePath. "/ratings";
        $userRating = $resourcePath . "/user_rating";


        $response = $this->get($resourcePath);


        $response->assertJson([
            'href' => $resourcePath,
            'id' => $amendment->id,
            'explanation' => $amendment->explanation,
            'changes' => [],
            'version' => $amendmentVersion,
            'ratings' => [
                'href' => $ratingsUri
            ],
            'user_rating' => [
                'href' => $userRating
            ],
            'tags' => [
                [
                    'id' => $tags[0]->id,
                    'name' => $tags[0]->name,
                    'description' => $tags[0]->description
                ],
                [
                    'id' => $tags[1]->id,
                    'name' => $tags[1]->name,
                    'description' => $tags[1]->description
                ]
            ],
            'author' => [
                'href' => $amendment->user->getResourcePath(),
                'id' => $amendment->user->id
            ],
            'subamendments' => [
                'href' => $subamendmentsPath
            ],
            'comments' => [
                'href' => $commentsPath
            ],
            'created_at' => $amendment->created_at->toIso8601String(),
            'updated_at' => $amendment->updated_at->toIso8601String()
        ]);
    }

    public function testAmendmentCollection(){
        $discussion = factory(Discussion::class)->states('user')->create();
        $temp_amendments = factory(Amendment::class, 3)->states('user')->create([
            'discussion_id' => $discussion->id
        ]);

        $amendments = Amendment::all()->where('discussion_id', '=', $discussion->id);

        $resourcePath = $this->baseURI . $discussion->getResourcePath() . "/amendments";
        $response = $this->get($resourcePath);

        $response->assertJson([
            'href' => $resourcePath,
            'amendments' => $amendments->transform(function($amendment){
                return [
                    'href'=> $amendment->getResourcePath(),
                    'id' => $amendment->id
                ];
            })->toArray()
        ]);
    }
}

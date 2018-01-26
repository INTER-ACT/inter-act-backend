<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class SubamendmentTests extends TestCase
{
    use ResourceTestTrait;

    /**
     * @test */
    public function testSubAmendmentResource()
    {
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment')->create()->fresh();

        $resourcePath = $this->baseURI . $subamendment->getResourcePath();
        $commentsPath = $resourcePath ."/comments";
        $ratingsPath = $resourcePath. "/ratings";
        $userRatingPath = $resourcePath . "/user_rating";

        $handled_at = $subamendment->handled_at === Null ? Null : $subamendment->handled_at->toIso8601String();

        $response = $this->get($resourcePath);

        $response->assertJson([
            'href' => $resourcePath,
            'id' => $subamendment->id,
            'explanation' => $subamendment->explanation,
            'created_at' => $subamendment->created_at->toIso8601String(),
            'author' => [
                'href' => $subamendment->user->getResourcePath(),
                'id' => $subamendment->user->id
            ],
            'amendment_version' => $subamendment->amendment_version,
            'ratings' => [
                'href' => $ratingsPath
            ],
            'user_rating' => [
                'href' => $userRatingPath
            ],
            'tags' => $subamendment->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            })->toArray(),
            'changes' => [],
            'comments' => [
                'href' => $commentsPath
            ],
            'status' => $subamendment->status,
            'handled_at' => $handled_at,
            'handle_explanation' => $subamendment->handle_explanation
        ]);
    }

    /**
     *
     * @test */
    public function testSubAmendmentCollection()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $subamendments = factory(SubAmendment::class, 3)->states('user')->create([
            'amendment_id' => $amendment->id
        ]);

        $resourcePath = $this->baseURI . $amendment->getResourcePath() . "/subamendments";
        $response = $this->get($resourcePath);

        $response->assertJson([
            'href' => $resourcePath,
            'subamendments' => $subamendments->transform(function($subamendment){
                return [
                    'href' => $this->baseURI . $subamendment->getResourcePath(),
                    'id' => $subamendment->id
                ];
            })->toArray()
        ]);
    }

}

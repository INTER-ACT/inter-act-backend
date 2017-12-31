<?php

namespace Tests\Unit;

use App\Discussions\Discussion;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class DiscussionTests extends TestCase
{
    use ResourceTestTrait;

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
            'created_at' => $discussion->created_at->toAtomString(),
            'updated_at' => $discussion->updated_at->toAtomString(),
            'law_text' => $discussion->law_text,
            'law_explanation' => $discussion->law_explanation,
            'author' => [
                'href' => $this->baseURI . $discussion->user->getResourcePath(),
                'id' => $discussion->user->id
            ],
            'amendments' => ['href' => $resourcePath . '/amendments'],
            'comments' => ['href' => $resourcePath . '/comments'],
            'tags' => $discussion->tags->transform(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description
                ];
            })->toArray()
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
                ],
                [
                    'href' => $this->baseURI . $discussion2->getResourcePath(),
                    'id' => $discussion2->id,
                    'title' => $discussion2->title
                ]
            ]
        ]);
    }
}

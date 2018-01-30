<?php

namespace Tests\Unit;

use App\Discussions\Discussion;
use App\Model\ModelFactory;
use App\Tags\Tag;
use App\User;
use Carbon\Carbon;
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
        $resourcePath = $this->getUrl($discussion->getResourcePath());
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
                'href' => $this->getUrl($discussion->user->getResourcePath()),
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
    public function testDiscussionCollectionPaginated()
    {
        $perPage = 8;
        $start = 0;
        $discussion_count = 2;

        $this->be(factory(User::class)->create());
        $discussion1 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion2 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $resourcePath = $this->getUrl('/discussions');
        $requestPath = $resourcePath . '?start=' . $start . '&count=' . $perPage;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertJson([
            "data" => [
                'href' => $requestPath,
                'discussions' => [
                    [
                        'href' => $this->getUrl($discussion1->getResourcePath()),
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                    ],
                    [
                        'href' => $this->getUrl($discussion2->getResourcePath()),
                        'id' => $discussion2->id,
                        'title' => $discussion2->title
                    ]
                ]
            ],
            "links" => [
                "first" => $resourcePath . '?count=' . $perPage . '&start=' . $start,
                "last" => $resourcePath . '?count=' . $perPage . '&start=' . $start,
                "prev" => null,
                "next" => null
            ],
            "meta" => [
                "current_page" => $start,
                "from" => 1,
                "last_page" => 1,
                "path" => $resourcePath,
                "per_page" => $perPage,
                "to" => 2,
                "total" => $discussion_count
            ]
        ]);
    }

    /** @test */
    public function testDiscussionCollectionPaginatedSortChronological()
    {
        $perPage = 8;
        $start = 0;
        $discussion_count = 2;

        $this->be(factory(User::class)->create());
        $tag = Tag::getSozialeMedien();
        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 2, 2));
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), null, [Tag::getUserGeneratedContent()]);
        $resourcePath = $this->getUrl('/discussions');
        $pathParams = 'count=' . $perPage . '&sorted_by=chronological&tag_id=' . $tag->id;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertJson([
            "data" => [
                'href' => $requestPath,
                'discussions' => [
                    [
                        'href' => $this->getUrl($discussion2->getResourcePath()),
                        'id' => $discussion2->id,
                        'title' => $discussion2->title
                    ],
                    [
                        'href' => $this->getUrl($discussion1->getResourcePath()),
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                    ]
                ]
            ],
            "links" => [
                "first" => $resourcePath . '?' . $pathParams . '&start=' . $start,
                "last" => $resourcePath . '?' . $pathParams . '&start=' . $start,
                "prev" => null,
                "next" => null
            ],
            "meta" => [
                "current_page" => $start,
                "from" => 1,
                "last_page" => 1,
                "path" => $resourcePath,
                "per_page" => $perPage,
                "to" => 2,
                "total" => $discussion_count
            ]
        ]);
    }
}

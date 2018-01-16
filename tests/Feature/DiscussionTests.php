<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.01.18
 * Time: 17:24
 */

namespace Tests\Feature;


use App\Amendments\Amendment;
use App\Discussions\Discussion;
use App\Model\ModelFactory;
use App\Tags\Tag;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class DiscussionTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testDiscussionRouteReturn()
    {
        $this->be(factory(User::class)->create());
        $discussion = factory(Discussion::class)->create([
            'user_id' => \Auth::id()
        ]);

        $resourcePath = $this->baseURI . $discussion->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertStatus(200);
        $response->assertExactJson([
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
    public function testDiscussionsRouteReturnNoParametersSet()
    {
        //default is count=100&start=1&sorted_by=popularity&sort_direction=desc
        $discussion_count = 2;

        $this->be(factory(User::class)->create());
        $discussion1 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion2 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), Carbon::createFromDate(2017, 10, 10));
        factory(Amendment::class)->states('user')->create(['discussion_id' => $discussion2->id]);

        $resourcePath = $this->baseURI . '/discussions';
        $requestPath = $resourcePath;
        $response = $this->get($requestPath);
        $response->assertJson([
            "data" => [
                'href' => $requestPath,
                'total' => 2,
                'discussions' => [
                    [
                        'href' => $this->baseURI . $discussion2->getResourcePath(),
                        'id' => $discussion2->id,
                        'title' => $discussion2->title
                    ],
                    [
                        'href' => $this->baseURI . $discussion1->getResourcePath(),
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                    ]
                ]
            ],
            "links" => [
                "first" => $resourcePath . '?start=1',
                "last" => $resourcePath . '?start=1',
                "prev" => null,
                "next" => null
            ],
            "meta" => [
                "current_page" => 1,
                "from" => 1,
                "last_page" => 1,
                "path" => $resourcePath,
                "per_page" => 100,
                "to" => 2,
                "total" => $discussion_count
            ]
        ]);
    }

    /** @test */
    public function testDiscussionsRouteReturnAllParametersSet()
    {
        $perPage = 2;
        $start = 0;
        $sorted_by = 'popularity';
        $sort_direction = 'asc';
        $discussion_count = 3;  //without wrong tag discussion

        $this->be(factory(User::class)->create());
        $tag = Tag::getSozialeMedien();
        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion4 = ModelFactory::CreateDiscussion(\Auth::user(), null, [Tag::getUserGeneratedContent()]);
        $resourcePath = $this->baseURI . '/discussions';
        $pathParams = 'count=' . $perPage . '&sorted_by=' . $sorted_by . '&sort_direction=' . $sort_direction . '&tag_id=' . $tag->id;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertJson([
            "data" => [
                'href' => $requestPath,
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
            ],
            "links" => [
                "first" => $resourcePath . '?' . $pathParams . '&start=' . $start,
                "last" => $resourcePath . '?' . $pathParams . '&start=' . 2,
                "prev" => null,
                "next" => $resourcePath . '?' . $pathParams . '&start=' . 2
            ],
            "meta" => [
                "current_page" => $start,
                "from" => 1,
                "last_page" => 2,
                "path" => $resourcePath,
                "per_page" => $perPage,
                "to" => 2,
                "total" => $discussion_count
            ]
        ]);
    }

    /** @test */
    public function testDiscussionsRouteReturnSortedByChronological()
    {
        $perPage = 10;
        $start = 0;
        $sorted_by = 'chronological';
        $sort_direction = 'asc';
        $discussion_count = 3;

        $this->be(factory(User::class)->create());
        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 3, 2));
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 2, 2));
        $resourcePath = $this->baseURI . '/discussions';
        $pathParams = 'count=' . $perPage . '&sorted_by=' . $sorted_by . '&sort_direction=' . $sort_direction;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertJson([
            "data" => [
                'href' => $requestPath,
                'total' => 3,
                'discussions' => [
                    [
                        'href' => $this->baseURI . $discussion1->getResourcePath(),
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                    ],
                    [
                        'href' => $this->baseURI . $discussion3->getResourcePath(),
                        'id' => $discussion3->id,
                        'title' => $discussion3->title
                    ],
                    [
                        'href' => $this->baseURI . $discussion2->getResourcePath(),
                        'id' => $discussion2->id,
                        'title' => $discussion2->title
                    ]
                ]
            ],
            "links" => [
                "first" => $resourcePath . '?' . $pathParams . '&start=' . $start,
                "last" => $resourcePath . '?' . $pathParams . '&start=' . 1,
                "prev" => null,
                "next" => null
            ],
            "meta" => [
                "current_page" => $start,
                "from" => 1,
                "last_page" => 1,
                "path" => $resourcePath,
                "per_page" => $perPage,
                "to" => 3,
                "total" => $discussion_count
            ]
        ]);
    }


}
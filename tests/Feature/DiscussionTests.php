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
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InvalidPaginationException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Exceptions\CustomExceptions\PayloadTooLargeException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\MultiAspectRatingResource;
use App\Http\Resources\PostResources\TagCollection;
use App\Model\ModelFactory;
use App\MultiAspectRating;
use App\Role;
use App\Tags\Tag;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Mockery\Exception;
use Tests\ApiTestTrait;
use Tests\FeatureTestCase;
use Tests\TestCase;
use Tests\Unit\MultiAspectRatingResourceTests;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class DiscussionTests extends FeatureTestCase
{
    use ApiTestTrait;

    //region /discussions
    /** @test */
    public function testDiscussionsRouteResponseNoParametersSet()
    {
        //default is count=100&start=1&sorted_by=popularity&sort_direction=desc
        $discussion_count = 2;

        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $discussion1 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion2 = factory(Discussion::class)->create(['user_id' => \Auth::id()]);
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), Carbon::createFromDate(2017, 10, 10));
        factory(Amendment::class)->states('user')->create(['discussion_id' => $discussion2->id]);

        $resourcePath = url('/discussions');
        $requestPath = $resourcePath;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
            "data" => [
                'href' => $requestPath,
                'discussions' => [
                    [
                        'href' => url($discussion2->getResourcePath()),
                        'id' => $discussion2->id,
                        'title' => $discussion2->title
                    ],
                    [
                        'href' => url($discussion1->getResourcePath()),
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
                "current_page" => PageRequest::DEFAULT_PAGE_NUMBER,
                "from" => 1,
                "last_page" => 1,
                "path" => $resourcePath,
                "per_page" => PageRequest::DEFAULT_PER_PAGE,
                "to" => 2,
                "total" => $discussion_count
            ]
        ]);
    }

    /** @test */
    public function testDiscussionsRouteResponseAllParametersSet()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $perPage = 2;
        $start = 0;
        $sorted_by = 'popularity';
        $sort_direction = 'asc';
        $discussion_count = 3;  //without wrong tag discussion

        $tag = Tag::getSozialeMedien();
        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), null, [$tag], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion4 = ModelFactory::CreateDiscussion(\Auth::user(), null, [Tag::getUserGeneratedContent()]);
        $resourcePath = url('/discussions');
        $pathParams = 'count=' . $perPage . '&sorted_by=' . $sorted_by . '&sort_direction=' . $sort_direction . '&tag_id=' . $tag->id;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(200)
            ->assertJson([
            "data" => [
                'href' => $requestPath,
                'discussions' => [
                    [
                        'href' => url($discussion1->getResourcePath()),
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                    ],
                    [
                        'href' => url($discussion2->getResourcePath()),
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
    public function testDiscussionsRouteResponseSortedByChronological()
    {
        $perPage = 10;
        $start = 0;
        $sorted_by = 'chronological';
        $sort_direction = 'asc';
        $discussion_count = 3;

        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 3, 2));
        $discussion3 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 2, 2));
        $resourcePath = url('/discussions');
        $pathParams = 'count=' . $perPage . '&sorted_by=' . $sorted_by . '&sort_direction=' . $sort_direction;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(200)
            ->assertJson([
            "data" => [
                'href' => $requestPath,
                'discussions' => [
                    [
                        'href' => url($discussion1->getResourcePath()),
                        'id' => $discussion1->id,
                        'title' => $discussion1->title
                    ],
                    [
                        'href' => url($discussion3->getResourcePath()),
                        'id' => $discussion3->id,
                        'title' => $discussion3->title
                    ],
                    [
                        'href' => url($discussion2->getResourcePath()),
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

    /** @test */
    public function testDiscussionsWithInvalidPaginationCountMin()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $perPage = 0;
        $start = 0;
        $discussion_count = 2;

        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 1, 2));
        $resourcePath = $this->getUrl('/discussions');
        $pathParams = 'count=' . $perPage;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(["code" => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testDiscussionsWithInvalidPaginationCountMax()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $perPage = 101;
        $start = 0;
        $discussion_count = 2;

        $discussion1 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 1, 2));
        $discussion2 = ModelFactory::CreateDiscussion(\Auth::user(), null, [], Carbon::createFromDate(2017, 1, 1, 2));
        $resourcePath = $this->getUrl('/discussions');
        $pathParams = 'count=' . $perPage;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(["code" => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testDiscussionsWithInvalidPaginationCountWrong()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $perPage = "asd";
        $start = 0;

        $resourcePath = $this->getUrl('/discussions');
        $pathParams = 'count=' . $perPage;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(["code" => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testDiscussionsWithInvalidPaginationStartWrong()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $perPage = 1;
        $start = "asd";

        $resourcePath = $this->getUrl('/discussions');
        $pathParams = 'count=' . $perPage;
        $requestPath = $resourcePath . '?start=' . $start . '&' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(["code" => InvalidPaginationException::ERROR_CODE]);
    }

    //TODO: invalid tag_id, ...
    //endregion

    //region post /discussions
    /** @test */
    public function testPostDiscussionsWithValidValuesAndAuthenticated()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $requestPath = $this->getUrl('/discussions');
        $tag1 = Tag::getSozialeMedien();
        $tag2 = Tag::getUserGeneratedContent();
        $inputData = [
            'title' => 'Titel',
            'law_text' => 'this and that',
            'law_explanation' => 'Law Explanation',
            'tags' => [
                    $tag1->id,
                    $tag2->id
                ]
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(201)
            ->assertJson([
                'href' => $this->getUrl(('/discussions/' . 1)),
                'id' => 1
            ]);
        //TODO: test if data is really there (get)
    }

    /** @test */
    public function testPostDiscussionsWithValidValuesNotPermitted()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $requestPath = $this->getUrl('/discussions');
        $tag1 = Tag::getSozialeMedien();
        $tag2 = Tag::getUserGeneratedContent();
        $inputData = [
            'title' => 'Titel',
            'law_text' => 'this and that',
            'law_explanation' => 'Law Explanation',
            'tags' => [
                $tag1->id,
                $tag2->id
            ]
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(NotPermittedException::HTTP_CODE)
            ->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testPostDiscussionsWithValidValuesNotAuthenticated()
    {
        $requestPath = $this->getUrl('/discussions');
        $tag1 = Tag::getSozialeMedien();
        $tag2 = Tag::getUserGeneratedContent();
        $inputData = [
            'title' => 'Titel',
            'law_text' => 'this and that',
            'law_explanation' => 'Law Explanation',
            'tags' => [
                $tag1->id,
                $tag2->id
            ]
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)
            ->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPostDiscussionsWithInvalidValuesAndAuthenticated()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $requestPath = $this->getUrl('/discussions');
        $tag1 = Tag::getSozialeMedien();
        $tag2 = Tag::getUserGeneratedContent();
        $inputData = [
            'title' => 'Titel',
            'law_text' => 'this and that',
            'law_explanation' => 1,
            'tags' => [
                $tag1->id,
                "abc"
            ]
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(InvalidValueException::HTTP_CODE)
            ->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }

    /** @test */
    public function testPostDiscussionSQLInjection()
    {
        $inputData = [
            'title' => 'Titel',
            'law_text' => 'this and that',
            'law_explanation' => "new exp'; DROP TABLE DISCUSSIONS;'",
            'tags' => [
                1
            ]
        ];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $requestPath = $this->getUrl('/discussions');
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(201);
        $response = $this->json('GET', $requestPath . '/' . 1);
        $response->assertStatus(200)->assertJson([
            'title' => $inputData['title'],
            'law_text' => $inputData['law_text'],
            'law_explanation' => $inputData['law_explanation']
        ]);
    }

    /** @test */
    public function testPostDiscussionInvalidTags()
    {
        $inputData = [
            'title' => 'Titel',
            'law_text' => 'this and that',
            'law_explanation' => "new exp",
            'tags' => [
                12
            ]
        ];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $requestPath = $this->getUrl('/discussions');
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }
    //endregion

    //region get /discussions/{id}
    /** @test */
    public function testDiscussionRouteResponse()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $discussion = factory(Discussion::class)->create([
            'user_id' => \Auth::id()
        ]);

        $resourcePath = $this->getUrl($discussion->getResourcePath());
        $response = $this->get($resourcePath);
        $response->assertStatus(200)
            ->assertJson(self::mapDiscussionToJson($discussion, $this->getUrl()));
    }   //TODO: remove? same as testOneDiscussionResponse?

    /** @test */
    public function testOneDiscussionResponse()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $tagCollection = collect($tags);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson(
                [
                    'href' => $this->getUrl($discussion->getResourcePath()),
                    'id' => $discussion->id,
                    'title' => $discussion->title,
                    'created_at' => $discussion->created_at->toAtomString(),
                    'updated_at' => $discussion->updated_at->toAtomString(),
                    'law_text' => $discussion->law_text,
                    'law_explanation' => $discussion->law_explanation,
                    'author' => [
                        'href' => $this->getUrl(\Auth::user()->getResourcePath()),
                        'id' => \Auth::id()
                    ],
                    'amendments' => [
                        'href' => $requestPath . '/amendments'
                    ],
                    'comments' => [
                        'href' => $requestPath . '/comments'
                    ],
                    'tags' => (new TagCollection($tagCollection))->toSubResourceArray(),
                    'rating' => $this->getUrl($discussion->getRatingPath())
                ]
            );
    }

    /** @test */
    public function testOneNonexistentDiscussionResponse()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $requestPath = $this->getUrl('/discussions/' . 1);
        $response = $this->get($requestPath);
        $response->assertStatus(404)
            ->assertJson([
                "code" => "Request_01"
            ]);
    }

    /** @test */
    public function testOneDiscussionWhenNotAuthenticatedResponse()
    {
        $tags = [Tag::getUserGeneratedContent(), Tag::getSozialeMedien()];
        $discussion = ModelFactory::CreateDiscussion(factory(User::class)->create(), null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $tagCollection = collect($tags);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $response = $this->get($requestPath);
        $response->assertStatus(200);
    }

    /** @test */
    public function testOneDiscussionResponseSQLInjection()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $requestPath = $this->getUrl('/discussions/' . $discussion->id . "'; DROP TABLE DISCUSSIONS;'");
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }
    //endregion

    //region update /discussions/{id}
    /** @test */
    public function testPatchDiscussionWithValidValuesAndAuthenticated()
    {
        $new_tag_ids = [1, 3];
        $new_law_explanation = "new explanation";

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(204);
        $getData = $this->json('GET', $requestPath);
        $getData->assertJson([
            'law_explanation' => $new_law_explanation,
            'tags' => array_map(function($item){
                return TagCollection::getSubResourceItemArray(Tag::find($item));
            }, $new_tag_ids)
        ]);
    }

    /** @test */
    public function testPatchDiscussionWithInvalidValuesAndAuthenticated()
    {
        $new_tag_ids = [1, 3];
        $new_law_explanation = 1;

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchDiscussionWithValidValuesAndNotAuthenticated()
    {
        $new_tag_ids = [1, 3];
        $new_law_explanation = "new explanation";

        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getExpert()), null, []);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchDiscussionWithValidValuesAndNotPermitted()
    {
        $new_tag_ids = [1, 3];
        $new_law_explanation = "new explanation";

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getScientist()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchNonexistentDiscussion()
    {
        $new_tag_ids = [1, 3];
        $new_law_explanation = "new explanation";

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $requestPath = $this->getUrl('/discussions/' . 1000);
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchDiscussionSQLInjection()
    {
        $new_tag_ids = [1, 3];
        $new_law_explanation = "new exp'; DROP TABLE DISCUSSIONS;'";

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $old_explanation = $discussion->law_explanation;
        $old_tags = $discussion->tags();
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $requestPath = $this->getUrl('/discussions/' . 1);
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(204);
        $response = $this->json('GET', $requestPath);
        $response->assertStatus(200)->assertJson([
            'law_explanation' => $new_law_explanation
        ]);
    }   //TODO: select injected element --> safe?

    /** @test */
    public function testPatchDiscussionInvalidTags()
    {
        $new_tag_ids = [0, 12];
        $new_law_explanation = "new exp";

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $old_explanation = $discussion->law_explanation;
        $old_tags = $discussion->tags();
        $inputData = [
            'law_explanation' => $new_law_explanation,
            'tags' => $new_tag_ids
        ];
        $requestPath = $this->getUrl('/discussions/' . 1);
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }
    //endregion

    //region delete /discussions/{id}
    /** @test */
    public function testArchiveAndFetchDiscussionAsAdminThenFetchAsExpert()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(204);
        $getData = $this->json('GET', $requestPath);
        $getData->assertStatus(200)->assertJson(self::mapDiscussionToJson($discussion, $this->getUrl()));
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getExpert()), ['*']
        );
        $getData = $this->json('GET', $requestPath);
        $getData->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testArchiveDiscussionAsExpert()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getExpert()), ['*']
        );
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testArchiveDiscussionNotAuthenticated()
    {
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()), null, []);
        $requestPath = $this->getUrl('/discussions/' . $discussion->id);
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testArchiveNonexistentDiscussion()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $requestPath = $this->getUrl('/discussions/' . 1000);
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }
    //endregion

    //region get /discussions/{id}/comments
    /** @test */
    public function testCommentsRouteResponseNoParametersSet()
    {
        $comment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags);

        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $requestPath = $resourcePath;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'comments' => [
                        [
                            'href' => $this->getUrl($comment2->getResourcePath()),
                            'id' => $comment2->id
                        ],
                        [
                            'href' => $this->getUrl($comment1->getResourcePath()),
                            'id' => $comment1->id
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
                    "current_page" => PageRequest::DEFAULT_PAGE_NUMBER,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => PageRequest::DEFAULT_PER_PAGE,
                    "to" => 2,
                    "total" => $comment_count
                ]
            ]);
    }

    /** @test */
    public function testCommentsRouteResponseWithValidPagination()
    {
        $start = 0;
        $count = 10;
        $comment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags);

        $params = 'count=' . $count;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?start=' . $start . '&' . $params;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'comments' => [
                        [
                            'href' => $this->getUrl($comment2->getResourcePath()),
                            'id' => $comment2->id
                        ],
                        [
                            'href' => $this->getUrl($comment1->getResourcePath()),
                            'id' => $comment1->id
                        ]
                    ]
                ],
                "links" => [
                    "first" => $resourcePath . '?' . $params . '&start=1',
                    "last" => $resourcePath . '?' . $params . '&start=1',
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "current_page" => $start,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => $count,
                    "to" => 2,
                    "total" => $comment_count
                ]
            ]);
    }

    /** @test */
    public function testCommentsRouteResponseWithInvalidPaginationMin()
    {
        $start = 0;
        $count = 0;
        $comment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags);

        $params = 'count=' . $count;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?start=' . $start . '&' . $params;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testCommentsRouteResponseWithInvalidPaginationMax()
    {
        $start = 0;
        $count = PageRequest::MAX_PER_PAGE + 1;
        $comment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags);

        $params = 'count=' . $count;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?start=' . $start . '&' . $params;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testCommentsRouteResponseWithInvalidPaginationCountWrong()
    {
        $start = 0;
        $count = "lol";
        $comment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags);

        $params = 'count=' . $count;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?start=' . $start . '&' . $params;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testCommentsRouteResponseWithInvalidPaginationStartWrong()
    {
        $start = "asd";
        $count = 5;
        $comment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $discussion, $tags);

        $params = 'count=' . $count;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?start=' . $start . '&' . $params;
        $response = $this->get($requestPath);
        if($start == 0) $start = 1;
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }
    //endregion

    //region post /discussions/{id}/comments
    /** @test */
    public function testPostCommentsValid()
    {
        $tags = collect([Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()]);
        $content = 'newly created comment content';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));

        $requestPath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tags->pluck('id')->toArray()
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(201)
            ->assertJson([
                'href' => url('/comments/' . 1),
                'id' => 1
            ]);
    }

    /** @test */
    public function testPostCommentsNotAuthorized()
    {
        $tags = collect([Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()]);
        $content = 'newly created comment content';

        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));

        $requestPath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tags->pluck('id')->toArray()
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPostCommentsInvalidTags()
    {
        $tag_ids = [12];
        $content = 'newly created comment content';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));

        $requestPath = $this->getUrl($discussion->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tag_ids
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }
    //endregion

    //region get /discussions/{id}/amendments
    /** @test */
    public function testAmendmentsRouteResponseNoParametersSet()    //TODO: test popularity with rating as well
    {
        $amendment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $amendment2 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags);
        ModelFactory::CreateComment(\Auth::user(), $amendment2, $tags);
        //$rating_aspect = ModelFactory::CreateRatingAspect('fair');
        //ModelFactory::CreateRating(\Auth::user(), $amendment2, $rating_aspect);

        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'amendments' => [
                        [
                            'href' => $this->getUrl($amendment2->getResourcePath()),
                            'id' => $amendment2->id
                        ],
                        [
                            'href' => $this->getUrl($amendment1->getResourcePath()),
                            'id' => $amendment1->id
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
                    "current_page" => PageRequest::DEFAULT_PAGE_NUMBER,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => PageRequest::DEFAULT_PER_PAGE,
                    "to" => 2,
                    "total" => $amendment_count
                ]
            ]);
    }

    /** @test */
    public function testAmendmentsRouteResponsePaginationValidSortDirectionAsc()
    {
        $start = 1;
        $count = 10;
        $sort_direction = 'asc';

        $amendment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $amendment2 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags);
        ModelFactory::CreateComment(\Auth::user(), $amendment2, $tags);
        //$rating_aspect = ModelFactory::CreateRatingAspect('fair');
        //ModelFactory::CreateRating(\Auth::user(), $amendment2, $rating_aspect);

        $params = 'count=' . $count . '&sort_direction=' . $sort_direction . '&start=' . $start;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'amendments' => [
                        [
                            'href' => $this->getUrl($amendment1->getResourcePath()),
                            'id' => $amendment1->id
                        ],
                        [
                            'href' => $this->getUrl($amendment2->getResourcePath()),
                            'id' => $amendment2->id
                        ]
                    ]
                ],
                "links" => [
                    "first" => $requestPath,
                    "last" => $requestPath,
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "current_page" => $start,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => $count,
                    "to" => 2,
                    "total" => $amendment_count
                ]
            ]);
    }

    /** @test */
    public function testAmendmentsRouteResponseSortedByChronological()
    {
        $sorted_by = 'chronological';

        $amendment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $amendment2 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags);

        $params = 'sorted_by=' . $sorted_by;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'amendments' => [
                        [
                            'href' => $this->getUrl($amendment2->getResourcePath()),
                            'id' => $amendment2->id
                        ],
                        [
                            'href' => $this->getUrl($amendment1->getResourcePath()),
                            'id' => $amendment1->id
                        ]
                    ]
                ],
                "links" => [
                    "first" => $requestPath . '&start=1',
                    "last" => $requestPath . '&start=1',
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "current_page" => PageRequest::DEFAULT_PAGE_NUMBER,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => PageRequest::DEFAULT_PER_PAGE,
                    "to" => 2,
                    "total" => $amendment_count
                ]
            ]);
    }

    /** @test */
    public function testAmendmentsRouteResponseSortedByChronologicalAsc()
    {
        $sorted_by = 'chronological';
        $sort_direction = 'asc';

        $amendment_count = 2;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $amendment2 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags);

        $params = 'sorted_by=' . $sorted_by . '&sort_direction=' . $sort_direction;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'amendments' => [
                        [
                            'href' => $this->getUrl($amendment1->getResourcePath()),
                            'id' => $amendment1->id
                        ],
                        [
                            'href' => $this->getUrl($amendment2->getResourcePath()),
                            'id' => $amendment2->id
                        ]
                    ]
                ],
                "links" => [
                    "first" => $requestPath . '&start=1',
                    "last" => $requestPath . '&start=1',
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "current_page" => PageRequest::DEFAULT_PAGE_NUMBER,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => PageRequest::DEFAULT_PER_PAGE,
                    "to" => 2,
                    "total" => $amendment_count
                ]
            ]);
    }

    /** @test */
    public function testAmendmentsRouteResponseInvalidPaginationMin()
    {
        $start = 1;
        $count = 0;

        $amendment_count = 1;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testAmendmentsRouteResponseInvalidPaginationMax()
    {
        $start = 1;
        $count = PageRequest::MAX_PER_PAGE + 1;

        $amendment_count = 1;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testAmendmentsRouteResponseInvalidPaginationCountWrong()
    {
        $start = 1;
        $count = "asd";

        $amendment_count = 1;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testAmendmentsRouteResponseInvalidPaginationStartWrong()
    {
        $start = "asd";
        $count = 10;

        $amendment_count = 1;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $amendment1 = ModelFactory::CreateAmendment(\Auth::user(), $discussion, $tags, Carbon::createFromDate(2017, 12, 31, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    //TODO? should an error be thrown for wrong sorted_by or sort_direction or just default (as it is currently)
    //endregion

    //region post /discussions/{id}/amendments
    /** @test */
    public function testPostAmendmentsValid()
    {
        $tags = collect([Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()]);
        $explanation = 'newly created amendment explanation';
        $updated_text = 'updated law_text';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));

        $requestPath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $inputData = [
            'explanation' => $explanation,
            'updated_text' => $updated_text,
            'tags' => $tags->pluck('id')->toArray()
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(201)
            ->assertJson([
                'href' => $requestPath . '/' . 1,
                'id' => 1
            ]);
    }

    /** @test */
    public function testPostAmendmentsNotAuthorized()
    {
        $tags = collect([Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()]);
        $explanation = 'newly created amendment explanation';
        $updated_text = 'updated law_text';

        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));

        $requestPath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $inputData = [
            'explanation' => $explanation,
            'updated_text' => $updated_text,
            'tags' => $tags->pluck('id')->toArray()
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPostAmendmentsInvalidTags()
    {
        $tag_ids = [12];
        $explanation = 'newly created amendment explanation';
        $updated_text = 'updated law_text';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));

        $requestPath = $this->getUrl($discussion->getResourcePath() . '/amendments');
        $inputData = [
            'explanation' => $explanation,
            'updated_text' => $updated_text,
            'tags' => $tag_ids
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }
    //endregion

    //region get /discussions/{id}/rating
    /** @test */
    public function testGetDiscussionRatingValidAndAuthenticated()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()), ['*']);
        $discussion = ModelFactory::CreateDiscussion(\Auth::user());
        $other_rating = ModelFactory::CreateMultiAspectRating(ModelFactory::CreateUser(Role::getStandardUser()), $discussion);
        $user_rating = ModelFactory::CreateMultiAspectRating(\Auth::user(), $discussion);
        $user_rating = MultiAspectRatingResourceTests::getArrayFromRating($user_rating);
        $other_rating = MultiAspectRatingResourceTests::getArrayFromRating($other_rating);
        $total_rating = [MultiAspectRating::ASPECT1 => 0, MultiAspectRating::ASPECT2 => 0, MultiAspectRating::ASPECT3 => 0, MultiAspectRating::ASPECT4 => 0, MultiAspectRating::ASPECT5 => 0, MultiAspectRating::ASPECT6 => 0, MultiAspectRating::ASPECT7 => 0, MultiAspectRating::ASPECT8 => 0, MultiAspectRating::ASPECT9 => 0, MultiAspectRating::ASPECT10 => 0];
        foreach($user_rating as $key => $item)
        {
            if($user_rating[$key])
                $total_rating[$key]++;
            if($other_rating[$key])
                $total_rating[$key]++;
        }

        $requestPath = $this->getUrl($discussion->getRatingPath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)->assertJson([
            'href' => $requestPath,
            'user_rating' => $user_rating,
            'total_rating' => $total_rating
        ]);
    }

    /** @test */
    public function testGetDiscussionRatingValidNotAuthenticated()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user);
        $other_rating = ModelFactory::CreateMultiAspectRating(ModelFactory::CreateUser(Role::getStandardUser()), $discussion);
        $user_rating = ModelFactory::CreateMultiAspectRating($user, $discussion);
        $user_rating = MultiAspectRatingResourceTests::getArrayFromRating($user_rating);
        $other_rating = MultiAspectRatingResourceTests::getArrayFromRating($other_rating);
        $total_rating = [MultiAspectRating::ASPECT1 => 0, MultiAspectRating::ASPECT2 => 0, MultiAspectRating::ASPECT3 => 0, MultiAspectRating::ASPECT4 => 0, MultiAspectRating::ASPECT5 => 0, MultiAspectRating::ASPECT6 => 0, MultiAspectRating::ASPECT7 => 0, MultiAspectRating::ASPECT8 => 0, MultiAspectRating::ASPECT9 => 0, MultiAspectRating::ASPECT10 => 0];
        foreach($user_rating as $key => $item)
        {
            if($user_rating[$key])
                $total_rating[$key]++;
            if($other_rating[$key])
                $total_rating[$key]++;
        }

        $requestPath = $this->getUrl($discussion->getRatingPath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)->assertJson([
            'href' => $requestPath,
            'total_rating' => $total_rating
        ]);
    }

    /** @test */
    public function testGetDiscussionRatingInvalidDiscussionId()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user);
        $other_rating = ModelFactory::CreateMultiAspectRating(ModelFactory::CreateUser(Role::getStandardUser()), $discussion);
        $user_rating = ModelFactory::CreateMultiAspectRating($user, $discussion);
        $user_rating = MultiAspectRatingResourceTests::getArrayFromRating($user_rating);
        $other_rating = MultiAspectRatingResourceTests::getArrayFromRating($other_rating);
        $total_rating = [MultiAspectRating::ASPECT1 => 0, MultiAspectRating::ASPECT2 => 0, MultiAspectRating::ASPECT3 => 0, MultiAspectRating::ASPECT4 => 0, MultiAspectRating::ASPECT5 => 0, MultiAspectRating::ASPECT6 => 0, MultiAspectRating::ASPECT7 => 0, MultiAspectRating::ASPECT8 => 0, MultiAspectRating::ASPECT9 => 0, MultiAspectRating::ASPECT10 => 0];
        foreach($user_rating as $key => $item)
        {
            if($user_rating[$key])
                $total_rating[$key]++;
            if($other_rating[$key])
                $total_rating[$key]++;
        }

        $requestPath = $this->getUrl("/discussions/12/rating");
        $response = $this->get($requestPath);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }
    //endregion

    //region put /discussions/{id}/rating

    //endregion

    /**
     * @param Discussion $discussion
     * @param string $baseUri
     * @return array
     */
    private static function mapDiscussionToJson(Discussion $discussion, string $baseUri) : array
    {
        $resourcePath = $baseUri . $discussion->getResourcePath();
        return [
            'href' => $baseUri . $discussion->getResourcePath(),
            'id' => $discussion->id,
            'title' => $discussion->title,
            'created_at' => $discussion->created_at->toAtomString(),
            'updated_at' => $discussion->updated_at->toAtomString(),
            'law_text' => $discussion->law_text,
            'law_explanation' => $discussion->law_explanation,
            'author' => [
                'href' => $baseUri . $discussion->user->getResourcePath(),
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
            })->toArray(),
            'rating' => config('app.url') . $discussion->getRatingPath()
        ];
    }
}
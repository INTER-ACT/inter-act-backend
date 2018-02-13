<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 25.01.18
 * Time: 17:35
 */

namespace Tests\Feature;


use App\Domain\Manipulators\CommentManipulator;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InvalidPaginationException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\MissingArgumentException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Exceptions\CustomExceptions\PayloadTooLargeException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\PostResources\TagCollection;
use App\Model\ModelFactory;
use App\Role;
use App\Tags\Tag;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use Tests\ApiTestTrait;
use Tests\FeatureTestCase;

class CommentTests extends FeatureTestCase
{
    //region get /comments
    /** @test */
    public function testCommentsRouteResponseNoParametersSet()
    {
        $comment_count = 3;
        $tags = [Tag::getWirtschaftlicheInteressen(), Tag::getDownloadUndStreaming()];

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion1 = ModelFactory::CreateDiscussion($user);
        $comment1 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 1, 12, 2));
        $comment2 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 24, 12, 2));
        $comment3 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 12, 12, 2));

        $resourcePath = $this->getUrl('/comments');
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
                            'href' => $this->getUrl($comment3->getResourcePath()),
                            'id' => $comment3->id
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
                    "to" => $comment_count,
                    "total" => $comment_count
                ]
            ]);
    }

    /** @test */
    public function testCommentsRouteResponsePaginationSet()
    {
        $count = 5;
        $start = 1;
        $comment_count = 3;
        $tags = [Tag::getWirtschaftlicheInteressen(), Tag::getDownloadUndStreaming()];

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion1 = ModelFactory::CreateDiscussion($user);
        $comment1 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 1, 12, 2));
        $comment2 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 24, 12, 2));
        $comment3 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 12, 12, 2));

        $resourcePath = $this->getUrl('/comments');
        $pathParams = 'count=' . $count . '&start=' . $start;
        $requestPath = $resourcePath . '?' . $pathParams;
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
                            'href' => $this->getUrl($comment3->getResourcePath()),
                            'id' => $comment3->id
                        ],
                        [
                            'href' => $this->getUrl($comment1->getResourcePath()),
                            'id' => $comment1->id
                        ]
                    ]
                ],
                "links" => [
                    "first" => $resourcePath . '?' . $pathParams,
                    "last" => $resourcePath . '?' . $pathParams,
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "current_page" => $start,
                    "from" => 1,
                    "last_page" => 1,
                    "path" => $resourcePath,
                    "per_page" => $count,
                    "to" => $comment_count,
                    "total" => $comment_count
                ]
            ]);
    }

    /** @test */
    public function testCommentsRouteResponseInvalidPaginationMin()
    {
        $count = 0;
        $start = 1;
        $comment_count = 3;
        $tags = [Tag::getWirtschaftlicheInteressen(), Tag::getDownloadUndStreaming()];

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion1 = ModelFactory::CreateDiscussion($user);
        $comment1 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 1, 12, 2));
        $comment2 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 24, 12, 2));
        $comment3 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 12, 12, 2));

        $resourcePath = $this->getUrl('/comments');
        $pathParams = 'count=' . $count . '&start=' . $start;
        $requestPath = $resourcePath . '?' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testCommentsRouteResponseInvalidPaginationMax()
    {
        $count = PageRequest::MAX_PER_PAGE + 1;
        $start = 1;
        $comment_count = 3;
        $tags = [Tag::getWirtschaftlicheInteressen(), Tag::getDownloadUndStreaming()];

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion1 = ModelFactory::CreateDiscussion($user);
        $comment1 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 1, 12, 2));
        $comment2 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 24, 12, 2));
        $comment3 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 12, 12, 2));

        $resourcePath = $this->getUrl('/comments');
        $pathParams = 'count=' . $count . '&start=' . $start;
        $requestPath = $resourcePath . '?' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testCommentsRouteResponseInvalidPaginationCountWrong()
    {
        $count = "asd";
        $start = 1;
        $comment_count = 3;
        $tags = [Tag::getWirtschaftlicheInteressen(), Tag::getDownloadUndStreaming()];

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion1 = ModelFactory::CreateDiscussion($user);
        $comment1 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 1, 12, 2));
        $comment2 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 24, 12, 2));
        $comment3 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 12, 12, 2));

        $resourcePath = $this->getUrl('/comments');
        $pathParams = 'count=' . $count . '&start=' . $start;
        $requestPath = $resourcePath . '?' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testCommentsRouteResponseInvalidPaginationStartWrong()
    {
        $count = 5;
        $start = "lol";
        $comment_count = 3;
        $tags = [Tag::getWirtschaftlicheInteressen(), Tag::getDownloadUndStreaming()];

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion1 = ModelFactory::CreateDiscussion($user);
        $comment1 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 1, 12, 2));
        $comment2 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 24, 12, 2));
        $comment3 = ModelFactory::CreateComment($user, $discussion1, $tags, Carbon::createFromDate(2017, 12, 12, 2));

        $resourcePath = $this->getUrl('/comments');
        $pathParams = 'count=' . $count . '&start=' . $start;
        $requestPath = $resourcePath . '?' . $pathParams;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }
    //endregion

    //region get /comments/{id}
    /** @test */
    public function testOneCommentResponseAuthenticated()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $comment = ModelFactory::CreateComment($user, $discussion, $tags);
        ModelFactory::CreateCommentRating($user, $comment, 1);
        Passport::actingAs($user, ['*']);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getExpert()), $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, -1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, -1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        $tagCollection = collect($tags);

        $requestPath = url($comment->getResourcePath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson(
                [
                    'href' => url($comment->getResourcePath()),
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->toAtomString(),
                    'parent' => [
                        'id' => $comment->id,
                        'href' => url($discussion->getResourcePath())
                    ],
                    'author' => [
                        'href' => url($user->getResourcePath()),
                        'id' => $user->id
                    ],
                    'comments' => [
                        'href' => url($requestPath . '/comments')
                    ],
                    'tags' => (new TagCollection($tagCollection))->toSubResourceArray(),
                    'positive_ratings' => 4,
                    'negative_ratings' => 2,
                    'user_rating' => 1
                ]
            );
    }

    /** @test */
    public function testOneCommentResponseNotAuthenticated()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $comment = ModelFactory::CreateComment($user, $discussion, $tags);
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getExpert()), $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, -1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, -1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        $tagCollection = collect($tags);

        $requestPath = url($comment->getResourcePath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson(
                [
                    'href' => url($comment->getResourcePath()),
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->toAtomString(),
                    'parent' => [
                        'id' => $comment->id,
                        'href' => url($discussion->getResourcePath())
                    ],
                    'author' => [
                        'href' => url($user->getResourcePath()),
                        'id' => $user->id
                    ],
                    'comments' => [
                        'href' => url($requestPath . '/comments')
                    ],
                    'tags' => (new TagCollection($tagCollection))->toSubResourceArray(),
                    'positive_ratings' => 4,
                    'negative_ratings' => 2,
                    'user_rating' => null
                ]
            );
    }

    /** @test */
    public function testOneCommentResponseAuthenticatedButNotRated()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $comment = ModelFactory::CreateComment($user, $discussion, $tags);
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, -1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, -1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        $tagCollection = collect($tags);

        $requestPath = url($comment->getResourcePath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson(
                [
                    'href' => url($comment->getResourcePath()),
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->toAtomString(),
                    'parent' => [
                        'id' => $comment->id,
                        'href' => url($discussion->getResourcePath())
                    ],
                    'author' => [
                        'href' => url($user->getResourcePath()),
                        'id' => $user->id
                    ],
                    'comments' => [
                        'href' => url($requestPath . '/comments')
                    ],
                    'tags' => (new TagCollection($tagCollection))->toSubResourceArray(),
                    'positive_ratings' => 3,
                    'negative_ratings' => 2,
                    'user_rating' => null
                ]
            );
    }

    /** @test */
    public function testOneNonExistentCommentResponse()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $tagCollection = collect($tags);

        $requestPath = url('/comments/' . 1);
        $response = $this->get($requestPath);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }

    /** @test */
    public function testOneCommentResponseSQLInjection()
    {
        $tags = [Tag::getSozialeMedien(), Tag::getUserGeneratedContent()];
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, $tags, Carbon::createFromDate(2017, 1, 1, 2));
        $comment = ModelFactory::CreateComment($user, $discussion, $tags);
        $requestPath = $this->getUrl('/comments/' . $comment->id . "'; DROP TABLE DISCUSSIONS;'");
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }
    //endregion

    //region delete /comments/{id}
    /** @test */
    public function testDeleteCommentAsAdminThenFetch()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(204);
        $getData = $this->json('GET', $requestPath);
        $getData->assertStatus(200)->assertJson([
            'href' => url($comment->getResourcePath()),
            'id' => $comment->id,
            'content' => CommentManipulator::DELETED_COMMENT_CONTENT
        ]);
    }

    /** @test */
    public function testDeleteCommentAsExpert()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getExpert()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testDeleteCommentNotAuthenticated()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, []);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testDeleteNonExistentCommentAsAdmin()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(\Auth::user(), null, []);
        $requestPath = $this->getUrl('/comments/' . 1);
        $response = $this->json('DELETE', $requestPath);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }
    //endregion

    //region patch /comments/{id}
    /** @test */
    public function testPatchCommentWithValidValuesAndAuthenticated()
    {
        Tag::getWirtschaftlicheInteressen();
        $new_tag_ids = [1, 3];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()), null, []);
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $inputData = [
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(204);
        $getData = $this->json('GET', $requestPath);
        $getData->assertJson([
            'tags' => array_map(function($item){
                return TagCollection::getSubResourceItemArray(Tag::find($item));
            }, $new_tag_ids)
        ]);
    }

    /** @test */
    public function testPatchCommentWithValidValuesAndNotAuthenticated()
    {
        $new_tag_ids = [1, 3];
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, []);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $inputData = [
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchCommentWithValidValuesAndNotPermitted()
    {
        $new_tag_ids = [1, 3];
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()), ['*']);
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, []);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $inputData = [
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchNonexistentComment()
    {
        $new_tag_ids = [1, 3];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()), null, []);
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl('/comments/' . 10);
        $inputData = [
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }

    /** @test */
    public function testPatchCommentInvalidTags()
    {
        $new_tag_ids = [1, 12];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()), null, []);
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath());
        $inputData = [
            'tags' => $new_tag_ids
        ];
        $response = $this->json('PATCH', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }
    //endregion

    //region put /comments/{id}/user_rating
    /** @test */
    public function testPutCommentRatingValid()
    {
        $user_rating = 1;

        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()));
        $discussion = ModelFactory::CreateDiscussion(\Auth::user());
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl($comment->getResourcePath() . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(204);
        $getData = $this->json('GET', $this->getUrl($comment->getResourcePath()));
        $getData->assertStatus(200)
            ->assertJson([
            'user_rating' => $user_rating
        ]);
    }

    /** @test */
    public function testPutCommentRatingValidOnExisting()
    {
        $user_rating = -1;

        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()));
        $discussion = ModelFactory::CreateDiscussion(\Auth::user());
        $comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating(\Auth::user(), $comment, 1);
        $requestPath = $this->getUrl($comment->getResourcePath() . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(204);
        $getData = $this->json('GET', $this->getUrl($comment->getResourcePath()));
        $getData->assertStatus(200)
            ->assertJson([
                'positive_ratings' => 1,
                'negative_ratings' => 1,
                'user_rating' => $user_rating
            ]);
    }

    /** @test */
    public function testPutCommentRatingNotAuthenticated()
    {
        $user_rating = -1;

        $user = ModelFactory::CreateUser(Role::getStandardUser());
        $discussion = ModelFactory::CreateDiscussion($user);
        $comment = ModelFactory::CreateComment($user, $discussion);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $requestPath = $this->getUrl($comment->getResourcePath() . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPutCommentRatingOnNonexistentComment()
    {
        $user_rating = -1;
        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()));
        $user = ModelFactory::CreateUser(Role::getStandardUser());
        $discussion = ModelFactory::CreateDiscussion($user);
        $comment = ModelFactory::CreateComment($user, $discussion);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $requestPath = $this->getUrl('/comments/' . 1000 . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }

    /** @test */
    public function testPutCommentRatingUserRatingInvalid()
    {
        $user_rating = "lol";
        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()));
        $user = ModelFactory::CreateUser(Role::getStandardUser());
        $discussion = ModelFactory::CreateDiscussion($user);
        $comment = ModelFactory::CreateComment($user, $discussion);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $requestPath = $this->getUrl('/comments/' . 1000 . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }

    /** @test */
    public function testPutCommentRatingDeleteRating()
    {
        $user_rating = 0;
        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()));
        $user = ModelFactory::CreateUser(Role::getStandardUser());
        $discussion = ModelFactory::CreateDiscussion($user);
        $comment = ModelFactory::CreateComment($user, $discussion);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        ModelFactory::CreateCommentRating(\Auth::user(), $comment, 1);
        $requestPath = $this->getUrl($comment->getResourcePath() . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(204);
        $getData = $this->json('GET', $this->getUrl($comment->getResourcePath()));
        $getData->assertStatus(200)
            ->assertJson([
                'positive_ratings' => 1,
                'negative_ratings' => 0,
                'user_rating' => null
            ]);
    }

    /** @test */
    public function testPutCommentRatingDeleteRatingWhenNoRatingExists()
    {
        $user_rating = 0;
        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()));
        $user = ModelFactory::CreateUser(Role::getStandardUser());
        $discussion = ModelFactory::CreateDiscussion($user);
        $comment = ModelFactory::CreateComment($user, $discussion);
        ModelFactory::CreateCommentRating(ModelFactory::CreateUser(Role::getStandardUser()), $comment, 1);
        $requestPath = $this->getUrl($comment->getResourcePath() . '/user_rating');
        $inputData = [
            'user_rating' => $user_rating
        ];
        $response = $this->json('PUT', $requestPath, $inputData);
        $response->assertStatus(204);
        $getData = $this->json('GET', $this->getUrl($comment->getResourcePath()));
        $getData->assertStatus(200)
            ->assertJson([
                'positive_ratings' => 1,
                'negative_ratings' => 0,
                'user_rating' => null
            ]);
    }
    //endregion

    //region get /comments/{id}/comments
    /** @test */
    public function testSubCommentsRouteResponseNoParametersSet()
    {
        $comment_count = 3;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags);
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment3 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2018, 1, 1, 2));

        $resourcePath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
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
                            'href' => $this->getUrl($comment3->getResourcePath()),
                            'id' => $comment3->id
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
                    "to" => $comment_count,
                    "total" => $comment_count
                ]
            ]);
    }

    /** @test */
    public function testSubCommentsRouteResponseValidPagination()
    {
        $start = 1;
        $count = 2;

        $comment_count = 3;
        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags);
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment3 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2018, 1, 1, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'comments' => [
                        [
                            'href' => url($comment2->getResourcePath()),
                            'id' => $comment2->id
                        ],
                        [
                            'href' => url($comment3->getResourcePath()),
                            'id' => $comment3->id
                        ]
                    ]
                ],
                "links" => [
                    "first" => $requestPath,
                    "last" => $resourcePath . '?count=' . $count . '&start=' . 2,
                    "prev" => null,
                    "next" => $resourcePath . '?count=' . $count . '&start=' . 2
                ],
                "meta" => [
                    "current_page" => $start,
                    "from" => 1,
                    "last_page" => 2,
                    "path" => $resourcePath,
                    "per_page" => $count,
                    "to" => 2,
                    "total" => $comment_count
                ]
            ]);
    }

    /** @test */
    public function testSubCommentsRouteResponseInvalidPaginationMin()
    {
        $start = 1;
        $count = 0;

        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags);
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment3 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2018, 1, 1, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testSubCommentsRouteResponseInvalidPaginationMax()
    {
        $start = 1;
        $count = PageRequest::MAX_PER_PAGE + 1;

        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags);
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment3 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2018, 1, 1, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testSubCommentsRouteResponseInvalidPaginationStartWrong()
    {
        $start = "xd";
        $count = 10;

        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags);
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment3 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2018, 1, 1, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testSubCommentsRouteResponseInvalidPaginationCountWrong()
    {
        $start = 1;
        $count = "wut";

        $tags = [Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()];

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $comment1 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags);
        $comment2 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2017, 12, 31, 2));
        $comment3 = ModelFactory::CreateComment(\Auth::user(), $parent_comment, $tags, Carbon::createFromDate(2018, 1, 1, 2));

        $params = 'count=' . $count . '&start=' . $start;
        $resourcePath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $requestPath = $resourcePath . '?' . $params;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }
    //endregion

    //region post /comments/{id}/comments
    /** @test */
    public function testPostSubCommentsValid()
    {
        $tags = collect([Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()]);
        $content = 'newly created comment content';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);
        $requestPath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tags->pluck('id')->toArray()
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(201)
            ->assertJson([
                'href' => url('/comments/' . 2),
                'id' => 2
            ]);
    }

    /** @test */
    public function testPostSubCommentsNotAuthenticated()
    {
        $tags = collect([Tag::getSozialeMedien(), Tag::getWirtschaftlicheInteressen()]);
        $content = 'newly created comment content';

        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment($user, $discussion);

        $requestPath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tags->pluck('id')->toArray()
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testPostSubCommentsInvalidTagsString()
    {
        $tag_ids = ["asd"];
        $content = 'newly created comment content';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);

        $requestPath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tag_ids
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }

    /** @test */
    public function testPostSubCommentsInvalidTagsNonexistentRelation()
    {
        $tag_ids = [12];
        $content = 'newly created comment content';

        Passport::actingAs(
            ModelFactory::CreateUser(Role::getStandardUser()), ['*']
        );
        $discussion = ModelFactory::CreateDiscussion(ModelFactory::CreateUser(Role::getAdmin()));
        $parent_comment = ModelFactory::CreateComment(\Auth::user(), $discussion);

        $requestPath = $this->getUrl($parent_comment->getResourcePath() . '/comments');
        $inputData = [
            'content' => $content,
            'tags' => $tag_ids
        ];
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }
    //endregion

    //region post /tag_recommendations
    /** @test */
    public function testTagRecommendations()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        //$text = "Was das Internet in der Schule betrifft, kann ich nur zustimmen!";
        $text = "Internet Google Netz und was sonst so das Herz begehrt.";
        $request_path = $this->getUrl('/tag_recommendations');
        $response = $this->json('POST', $request_path, ['text' => $text]);
        $response->assertStatus(200);
        $data = (array)json_decode($response->content(), true);
        foreach ($data['tags'] as $key => $value) {
            $this->assertArrayHasKey('id', $value);
            $this->assertArrayHasKey('href', $value);
        }
    }

    /** @test */
    public function testTagRecommendationsNoText()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $text = "Was das Internet in der Schule betrifft, kann ich nur zustimmen!";
        $request_path = $this->getUrl('/tag_recommendations');
        $response = $this->json('POST', $request_path, []);
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }

    /** @test */
    public function testTagRecommendationsNotAuthenticated()
    {
        $text = "Was das Internet in der Schule betrifft, kann ich nur zustimmen!";
        $request_path = $this->getUrl('/tag_recommendations');
        $response = $this->json('POST', $request_path, ['text' => $text]);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testTagRecommendationsNotPermitted()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getExpert()), ['*']
        );
        $text = "Was das Internet in der Schule betrifft, kann ich nur zustimmen!";
        $request_path = $this->getUrl('/tag_recommendations');
        $response = $this->json('POST', $request_path, ['text' => $text]);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }
    //endregion
}
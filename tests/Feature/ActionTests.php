<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.01.18
 * Time: 20:04
 */

namespace Tests\Feature;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Domain\ActionRepository;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\InvalidPaginationException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Exceptions\CustomExceptions\PayloadTooLargeException;
use App\Model\ModelFactory;
use App\Role;
use App\Tags\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestResponse;
use Laravel\Passport\Passport;
use Tests\FeatureTestCase;

class ActionTests extends FeatureTestCase
{
    //region get /search
    /** @test */
    public function testSearchValidSearchTermOnly()
    {
        $search_term = Tag::USER_GENERATED_CONTENT_NAME;

        $valid_array = self::getAndCreateSearchableModelsGeneral();

        $resourcePath = $this->getUrl('/search');
        $requestPath = $resourcePath . '?search_term=' . $search_term;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /** @test */
    public function testSearchValidWithPaginationSet()
    {
        $search_term = 'yxcvbnm';
        $count = 5;
        $start = 2;

        $valid_array = self::getAndCreateSearchableModelsValidByContent();
        $actual = array_slice($valid_array, $count * ($start - 1), $count);
        $total = sizeof($valid_array);

        $resourcePath = $this->getUrl('/search');
        $params = '?search_term=' . $search_term . '&count=' . $count;
        $requestPathNoStart = $resourcePath . $params;
        $requestPath = $requestPathNoStart . '&start=' . $start;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    'href' => $requestPath,
                    'search_results' => $actual
                ],
                "links" => [
                    "first" => $requestPathNoStart. '&start=1',
                    "last" => $requestPathNoStart . '&start=' . ceil(((float)$total / (float)$count)),
                    "prev" => $requestPathNoStart . '&start=1',
                    "next" => null
                ],
                "meta" => [
                    "current_page" => $start,
                    "from" => 6,
                    "last_page" => ceil(((float)$total / (float)$count)),
                    "path" => $resourcePath,
                    "per_page" => $count,
                    "to" => $total,
                    "total" => $total
                ]
            ]);
    }

    /** @test */
    public function testSearchValidWithInvalidPaginationMin()
    {
        $search_term = 'yxcvbnm';
        $count = 0;
        $start = 2;

        $valid_array = self::getAndCreateSearchableModelsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $params = '?search_term=' . $search_term . '&count=' . $count;
        $requestPathNoStart = $resourcePath . $params;
        $requestPath = $requestPathNoStart . '&start=' . $start;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testSearchValidWithInvalidPaginationMax()
    {
        $search_term = 'yxcvbnm';
        $count = PageRequest::MAX_PER_PAGE + 1;
        $start = 1;

        $valid_array = self::getAndCreateSearchableModelsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $params = '?search_term=' . $search_term . '&count=' . $count;
        $requestPathNoStart = $resourcePath . $params;
        $requestPath = $requestPathNoStart . '&start=' . $start;
        $response = $this->get($requestPath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testSearchValidWithInvalidPaginationCountWrong()
    {
        $search_term = 'yxcvbnm';
        $count = "lol";
        $start = 2;

        $valid_array = self::getAndCreateSearchableModelsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $params = '?search_term=' . $search_term . '&count=' . $count;
        $requestPathNoStart = $resourcePath . $params;
        $requestPath = $requestPathNoStart . '&start=' . $start;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testSearchValidWithInvalidPaginationStartWrong()
    {
        $search_term = 'yxcvbnm';
        $count = 5;
        $start = 'asdf';

        $valid_array = self::getAndCreateSearchableModelsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $params = '?search_term=' . $search_term . '&count=' . $count;
        $requestPathNoStart = $resourcePath . $params;
        $requestPath = $requestPathNoStart . '&start=' . $start;
        $response = $this->get($requestPath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testSearchValidWithTypeTag()
    {
        $search_term = 'schaft';
        $type = 'tag';

        $valid_array = self::getAndCreateSearchableModelsValidByTag();
        $total = sizeof($valid_array);

        $resourcePath = $this->getUrl('/search');
        $params = '?search_term=' . $search_term . '&type=' . $type;
        $requestPathNoStart = $resourcePath . $params;
        $requestPath = $requestPathNoStart;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /** @test */
    public function testSearchValidWithTypeContent()
    {
        $search_term = 'yxcvbnm';
        $type = 'content';

        $valid_array = self::getAndCreateSearchableModelsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $requestPath = $resourcePath . '?search_term=' . $search_term . '&type=' . $type;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /** @test */
    public function testSearchValidWithContentTypeDiscussions()
    {
        $search_term = 'yxcvbnm';
        $content_type = 'discussions';

        $valid_array = self::getAndCreateDiscussionsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $requestPath = $resourcePath . '?search_term=' . $search_term . '&content_type=' . $content_type;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /** @test */
    public function testSearchValidWithContentTypeAmendments()
    {
        $search_term = 'yxcvbnm';
        $content_type = 'amendments';

        $valid_array = self::getAndCreateAmendmentsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $requestPath = $resourcePath . '?search_term=' . $search_term . '&content_type=' . $content_type;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /** @test */
    public function testSearchValidWithContentTypeSubAmendments()
    {
        $search_term = 'yxcvbnm';
        $content_type = 'subamendments';

        $valid_array = self::getAndCreateSubAmendmentsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $requestPath = $resourcePath . '?search_term=' . $search_term . '&content_type=' . $content_type;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /** @test */
    public function testSearchValidWithContentTypeComments()
    {
        $search_term = 'yxcvbnm';
        $content_type = 'comments';

        $valid_array = self::getAndCreateCommentsValidByContent();

        $resourcePath = $this->getUrl('/search');
        $requestPath = $resourcePath . '?search_term=' . $search_term . '&content_type=' . $content_type;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => ['href', 'search_results'],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "from", "last_page", "path", "per_page", "to", "total"]
            ]);
        foreach ($valid_array as $item)
        {
            $response->assertJsonFragment($item);
        }
        $response_search_results = json_decode(json_encode($response->json()), true)['data']['search_results'];
        $this->assertEquals(sizeof($valid_array), sizeof($response_search_results));
    }

    /**
     * @return array
     */
    protected function getAndCreateSearchableModelsGeneral() : array
    {
        $term = Tag::USER_GENERATED_CONTENT_NAME;
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussionValid1 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'title' => 'was ist ' . $term . '?'
        ]);

        $discussionValid2 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'law_text' => 'was ist yxcvbnm das?'
        ]);
        $discussionValid2->tags()->attach([Tag::getUserGeneratedContent()->id]);

        $discussionValid3 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'law_explanation' => 'yxcvbnm'
        ]);
        $discussionValid3->tags()->attach([Tag::getRechteinhaberschaft()->id, Tag::getUserGeneratedContent()->id]);

        $discussionInValid = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);

        $amendmentValid1 = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussionValid2->id,
            'updated_text' => 'lol' . $term . 'wut'
        ]);

        $amendmentValid2 = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussionValid1->id,
            'explanation' => 'ill explanin: yxcvbnm!'
        ]);
        $amendmentValid2->tags()->attach([Tag::getUserGeneratedContent()->id, Tag::getSozialeMedien()->id]);

        $amendmentInValid = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussionValid1->id,
            'updated_text' => 'as yxcvbn asdf'
        ]);

        $subamendmentValid1 = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendmentValid1->id,
            'updated_text' => 'lol' . $term . 'wut'
        ]);
        $subamendmentValid1->tags()->attach([Tag::getUserGeneratedContent()->id, Tag::getSozialeMedien()->id]);

        $subamendmentValid2 = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendmentValid1->id,
            'explanation' => 'lol' . $term . 'wut'
        ]);

        $subamendmentInValid = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendmentValid1->id,
            'updated_text' => 'as yxcv bnm asdf'
        ]);

        $commentValid1 = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $discussionInValid->id,
            'commentable_type' => $discussionInValid->getType(),
            'content' => 'ill explanin shortly: yxcvbnm!'
        ]);
        $commentValid1->tags()->attach([Tag::getUserGeneratedContent()->id, Tag::getSozialeMedien()->id]);

        $commentValid2 = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $discussionInValid->id,
            'commentable_type' => $discussionInValid->getType(),
            'content' => 'ill explanin shortly: ' . $term . '!'
        ]);

        $commentInValid = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $discussionValid1->id,
            'commentable_type' => $discussionValid1->getType(),
            'content' => 'as yxcv bnm asdf'
        ]);
        $commentInValid->tags()->attach([Tag::getRespektUndAnerkennung()->id, Tag::getSozialeMedien()->id]);

        $valid_array = [$discussionValid1, $discussionValid2, $discussionValid3, $amendmentValid1, $amendmentValid2, $subamendmentValid1, $subamendmentValid2, $commentValid1, $commentValid2];
        return array_map(function($item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id,
                'type' => $item->getApiFriendlyType()
            ];
        }, $valid_array);
    }

    /**
     * @return array
     */
    protected function getAndCreateSearchableModelsValidByTag() : array
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussionValid1 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'title' => 'was ist yxcvbnmdas?'
        ]);
        $discussionValid1->tags()->attach([Tag::getBildungUndWissenschaft()->id, Tag::getSozialeMedien()->id]);

        $discussionValid2 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'law_text' => 'was ist yxcvbnm das?'
        ]);
        $discussionValid2->tags()->attach([Tag::getBildungUndWissenschaft()->id]);

        $discussionValid3 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'law_explanation' => 'yxcvbnm'
        ]);
        $discussionValid3->tags()->attach([Tag::getRechteinhaberschaft()->id, Tag::getSozialeMedien()->id]);

        $discussionInValid = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);
        $discussionInValid->tags()->attach([Tag::getFreiheitenDerNutzer()->id, Tag::getSozialeMedien()->id]);

        $amendmentValid1 = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussionValid2->id,
            'updated_text' => 'lolxdyxcvbnmwut'
        ]);
        $amendmentValid1->tags()->attach([Tag::getBildungUndWissenschaft()->id, Tag::getSozialeMedien()->id]);

        $amendmentValid2 = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussionValid1->id,
            'explanation' => 'ill explanin: yxcvbnm!'
        ]);
        $amendmentValid2->tags()->attach([Tag::getBildungUndWissenschaft()->id, Tag::getSozialeMedien()->id]);

        $amendmentInValid = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussionValid1->id,
            'updated_text' => 'as yxcvbn asdf'
        ]);
        $amendmentInValid->tags()->attach([Tag::getUserGeneratedContent()->id, Tag::getSozialeMedien()->id]);

        $subamendmentValid1 = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendmentValid1->id,
            'updated_text' => 'lolxdyxcvbnmwut'
        ]);
        $subamendmentValid1->tags()->attach([Tag::getBildungUndWissenschaft()->id, Tag::getSozialeMedien()->id]);

        $subamendmentValid2 = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendmentValid1->id,
            'explanation' => 'ill explanin shortly: yxcvbnm!'
        ]);
        $subamendmentValid2->tags()->attach([Tag::getBildungUndWissenschaft()->id, Tag::getSozialeMedien()->id]);

        $subamendmentInValid = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendmentValid1->id,
            'updated_text' => 'as yxcv bnm asdf'
        ]);
        $subamendmentInValid->tags()->attach([Tag::getRespektUndAnerkennung()->id, Tag::getSozialeMedien()->id]);

        $commentValid = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $discussionInValid->id,
            'commentable_type' => $discussionInValid->getType(),
            'content' => 'ill explanin shortly: yxcvbnm!'
        ]);
        $commentValid->tags()->attach([Tag::getWirtschaftlicheInteressen()->id, Tag::getSozialeMedien()->id]);

        $commentInValid = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $discussionValid1->id,
            'commentable_type' => $discussionValid1->getType(),
            'content' => 'as yxcv bnm asdf'
        ]);
        $commentInValid->tags()->attach([Tag::getRespektUndAnerkennung()->id, Tag::getSozialeMedien()->id]);

        $valid_array = [$discussionValid1, $discussionValid2, $discussionValid3, $amendmentValid1, $amendmentValid2, $subamendmentValid1, $subamendmentValid2, $commentValid];
        return array_map(function($item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id,
                'type' => $item->getApiFriendlyType()
            ];
        }, $valid_array);
    }

    /**
     * @return array
     */
    protected function getAndCreateSearchableModelsValidByContent() : array
    {
        return array_merge($this->getAndCreateDiscussionsValidByContent(), $this->getAndCreateAmendmentsValidByContent(), $this->getAndCreateSubAmendmentsValidByContent(), $this->getAndCreateCommentsValidByContent());
    }

    /**
     * @return array
     */
    protected function getAndCreateDiscussionsValidByContent() : array
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussionValid1 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'title' => 'was ist yxcvbnmdas?'
        ]);

        $discussionValid2 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'law_text' => 'was ist yxcvbnm das?'
        ]);

        $discussionValid3 = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'law_explanation' => 'yxcvbnm'
        ]);

        $discussionInValid = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);

        $valid_array = [$discussionValid1, $discussionValid2, $discussionValid3];
        return array_map(function($item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id,
                'type' => $item->getApiFriendlyType()
            ];
        }, $valid_array);
    }

    /**
     * @return array
     */
    protected function getAndCreateAmendmentsValidByContent() : array
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $baseDiscussion = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'title' => '',
            'law_text' => '',
            'law_explanation' => ''
        ]);
        $amendmentValid1 = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $baseDiscussion->id,
            'updated_text' => 'lolxdyxcvbnmwut'
        ]);

        $amendmentValid2 = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $baseDiscussion->id,
            'explanation' => 'ill explanin: yxcvbnm!'
        ]);

        $amendmentInValid = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $baseDiscussion->id,
            'updated_text' => 'as yxcvbn asdf'
        ]);

        $valid_array = [$amendmentValid1, $amendmentValid2];
        return array_map(function($item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id,
                'type' => $item->getApiFriendlyType()
            ];
        }, $valid_array);
    }

    /**
     * @return array
     */
    protected function getAndCreateSubAmendmentsValidByContent() : array
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $baseDiscussion = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'title' => '',
            'law_text' => '',
            'law_explanation' => ''
        ]);
        $baseAmendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $baseDiscussion->id,
            'updated_text' => '',
            'explanation' => ''
        ]);
        $subamendmentValid1 = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $baseAmendment->id,
            'updated_text' => 'lolxdyxcvbnmwut'
        ]);

        $subamendmentValid2 = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $baseAmendment->id,
            'explanation' => 'ill explanin shortly: yxcvbnm!'
        ]);

        $subamendmentInValid = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $baseAmendment->id,
            'updated_text' => 'as yxcv bnm asdf'
        ]);

        $valid_array = [$subamendmentValid1, $subamendmentValid2];
        return array_map(function($item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id,
                'type' => $item->getApiFriendlyType()
            ];
        }, $valid_array);
    }

    /**
     * @return array
     */
    protected function getAndCreateCommentsValidByContent() : array
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $baseDiscussion = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'title' => '',
            'law_text' => '',
            'law_explanation' => ''
        ]);

        $commentValid = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $baseDiscussion->id,
            'commentable_type' => $baseDiscussion->getType(),
            'content' => 'ill explanin shortly: yxcvbnm!'
        ]);

        $commentInValid = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $baseDiscussion->id,
            'commentable_type' => $baseDiscussion->getType(),
            'content' => 'as yxcv bnm asdf'
        ]);

        $valid_array = [$commentValid];
        return array_map(function($item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id,
                'type' => $item->getApiFriendlyType()
            ];
        }, $valid_array);
    }
    //endregion

    //region get /statistics
    /** @test */
    public function testGeneralActivityStatisticsNoParameters()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()), ['*']);
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user, null, [], Carbon::createFromDate(2017, 01, 01));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], Carbon::createFromDate(2017, 07, 25));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, []);
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], Carbon::createFromDate(2017, 01, 01));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $rating = ModelFactory::CreateMultiAspectRating($user, $sub_amendment);

        $request_path = $this->getUrl('/statistics');
        $response = $this->get($request_path);
        $response->assertStatus(200);
    }

    /** @test */
    public function testGeneralActivityStatisticsAllParameters()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getScientist()), ['*']);
        $response = $this->getGeneralActivityStatisticsTestResponse(Carbon::createFromDate(2017, 1, 1), Carbon::createFromDate(2018, 1,1));
        $response->assertStatus(200);
    }

    /** @test */
    public function testGeneralActivityStatisticsNotAuthenticated()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], Carbon::createFromDate(2017, 01, 01));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], Carbon::createFromDate(2017, 07, 25));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, []);
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], Carbon::createFromDate(2017, 01, 01));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $rating = ModelFactory::CreateMultiAspectRating($user, $sub_amendment);

        $request_path = $this->getUrl('/statistics');
        $response = $this->get($request_path);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testGeneralActivityStatisticsNotPermitted()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getExpert()), ['*']);
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user, null, [], Carbon::createFromDate(2017, 01, 01));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], Carbon::createFromDate(2017, 07, 25));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, []);
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], Carbon::createFromDate(2017, 01, 01));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $rating = ModelFactory::CreateMultiAspectRating($user, $sub_amendment);

        $request_path = $this->getUrl('/statistics');
        $response = $this->get($request_path);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }

    /** @test */
    public function testGeneralActivityStatisticsBeginGreaterThanEnd()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getScientist()), ['*']);
        $response = $this->getGeneralActivityStatisticsTestResponse(Carbon::createFromDate(2018, 1, 1), Carbon::createFromDate(2017, 1,1));
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }

    /**
     * @param Carbon $begin
     * @param Carbon $end
     * @return TestResponse
     */
    public function getGeneralActivityStatisticsTestResponse(Carbon $begin, Carbon $end) : TestResponse
    {
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user, null, [], Carbon::createFromDate(2017, 01, 01));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], Carbon::createFromDate(2017, 07, 25));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, []);
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], Carbon::createFromDate(2017, 01, 01));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $rating = ModelFactory::CreateMultiAspectRating($user, $sub_amendment);

        $params = '?begin=' . $begin->toDateString() . '&end=' . $end->toDateString();
        $request_path = $this->getUrl('/statistics' . $params);
        $response = $this->get($request_path);
        return $response;
    }
    //endregion

    //region get /statistics/ratings

    //endregion

    //region get /statistics/comment_ratings

    //endregion

    //region get /job_list
    /** @test */
    public function testJobList()
    {
        $requestPath = $this->getUrl('/job_list');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'href' => $requestPath,
                'jobs' => ActionRepository::JOB_LIST
            ]);
    }
    //endregion

    //region get /graduation_list
    /** @test */
    public function testGraduationList()
    {
        $requestPath = $this->getUrl('/graduation_list');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'href' => $requestPath,
                'graduations' => ActionRepository::GRADUATION_LIST
            ]);
    }
    //endregion
}
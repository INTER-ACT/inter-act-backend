<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 24.01.18
 * Time: 17:47
 */

namespace Tests\Feature;


use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\InvalidPaginationException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\PayloadTooLargeException;
use App\Model\ModelFactory;
use App\Role;
use App\User;
use Laravel\Passport\Passport;
use Tests\ApiTestTrait;
use Tests\FeatureTestCase;
use Tests\TestCase;

class LawTests extends FeatureTestCase
{
    use ApiTestTrait;

    //region get /law_texts
    /** @test */
    public function testLawTextsResponseNoParameters()
    {
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $resourcePath = $this->getUrl('/law_texts');
        $response = $this->get($resourcePath);
        $response->assertStatus(200)
            ->assertSee('NOR')
            ->assertJsonFragment([
                'current_page' => PageRequest::DEFAULT_PAGE_NUMBER
            ])
            ->assertJsonFragment([
                'per_page' => PageRequest::DEFAULT_PER_PAGE
            ])
            ->assertJson([
                'data' => [
                    'href' => $this->getUrl('/law_texts')
                ]
            ]);
    }

    /** @test */
    public function testLawTextsResponseWithValidPagination()
    {
        $page_number = 1;
        $per_page = PageRequest::MAX_PER_PAGE;
        Passport::actingAs(
            ModelFactory::CreateUser(Role::getAdmin()), ['*']
        );
        $resourcePath = $this->getUrl('/law_texts?start=' . $page_number . '&count=' . $per_page);
        $response = $this->get($resourcePath);
        $response->assertStatus(200)
            ->assertSee('NOR')
            ->assertJsonFragment([
                'current_page' => 1
            ])
            ->assertJsonFragment([
                'per_page' => 100
            ])
            ->assertJson([
                'data' => [
                    'href' => $resourcePath
                ]
            ]);
    }

    /** @test */
    public function testLawTextsResponseWithInvalidPaginationMin()
    {
        $page_number = 1;
        $per_page = 0;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->getUrl('/law_texts?start=' . $page_number . '&count=' . $per_page);
        $response = $this->get($resourcePath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testLawTextsResponseWithInvalidPaginationMax()
    {
        $page_number = 1;
        $per_page = PageRequest::MAX_PER_PAGE + 1;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->getUrl('/law_texts?start=' . $page_number . '&count=' . $per_page);
        $response = $this->get($resourcePath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function testLawTextsResponseWithInvalidPaginationCountWrong()
    {
        $page_number = 1;
        $per_page = "asd";
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->getUrl('/law_texts?start=' . $page_number . '&count=' . $per_page);
        $response = $this->get($resourcePath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function testLawTextsResponseWithInvalidPaginationStartWrong()
    {
        $page_number = "asd";
        $per_page = 1;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->getUrl('/law_texts?start=' . $page_number . '&count=' . $per_page);
        $response = $this->get($resourcePath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }
    //endregion

    //TODO: test get law_texts/{id}
}
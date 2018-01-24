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
use App\User;
use Laravel\Passport\Passport;
use Tests\ApiTestTrait;
use Tests\TestCase;

class LawTests extends TestCase
{
    use ApiTestTrait;

    //region Discussions
    /** @test */
    public function LawTextsResponseNoParameters()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->baseURI . '/law_texts';
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
                    'href' => $this->baseURI . '/law_texts'
                ]
            ]);
    }

    /** @test */
    public function LawTextsResponseWithValidPagination()
    {
        $page_number = 1;
        $per_page = PageRequest::MAX_PER_PAGE;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->baseURI . '/law_texts?start=' . $page_number . '&count=' . $per_page;
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
    public function LawTextsResponseWithInvalidPaginationMin()
    {
        $page_number = 1;
        $per_page = 0;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->baseURI . '/law_texts?start=' . $page_number . '&count=' . $per_page;
        $response = $this->get($resourcePath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function LawTextsResponseWithInvalidPaginationMax()
    {
        $page_number = 1;
        $per_page = PageRequest::MAX_PER_PAGE + 1;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->baseURI . '/law_texts?start=' . $page_number . '&count=' . $per_page;
        $response = $this->get($resourcePath);
        $response->assertStatus(PayloadTooLargeException::HTTP_CODE)->assertJson(['code' => PayloadTooLargeException::ERROR_CODE]);
    }

    /** @test */
    public function LawTextsResponseWithInvalidPaginationWrongCountType()
    {
        $page_number = 1;
        $per_page = "asd";
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->baseURI . '/law_texts?start=' . $page_number . '&count=' . $per_page;
        $response = $this->get($resourcePath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }

    /** @test */
    public function LawTextsResponseWithInvalidPaginationWrongStartType()
    {
        $page_number = "asd";
        $per_page = 1;
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );
        $resourcePath = $this->baseURI . '/law_texts?start=' . $page_number . '&count=' . $per_page;
        $response = $this->get($resourcePath);
        $response->assertStatus(InvalidPaginationException::HTTP_CODE)->assertJson(['code' => InvalidPaginationException::ERROR_CODE]);
    }
    //endregion
}
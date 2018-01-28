<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 26.01.18
 * Time: 16:06
 */

namespace Tests\Feature;


use App\Model\ModelFactory;
use App\Role;
use Laravel\Passport\Passport;
use Tests\FeatureTestCase;

class TagTests extends FeatureTestCase
{
    /** @test */
    public function testTagsRouteResponse()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()), ['*']);
        $resourcePath = url('/tags');
        $requestPath = $resourcePath;
        $response = $this->get($requestPath);
        $response->assertStatus(200);
        $tags = json_decode($response->getContent(), true)['tags'];
        self::assertEquals(sizeof($tags), 10);
    }

    /** @test */
    public function testTagsRouteResponseNotAuthenticated()
    {
        $resourcePath = url('/tags');
        $requestPath = $resourcePath;
        $response = $this->get($requestPath);
        $response->assertStatus(200);
        $tags = json_decode($response->getContent(), true)['tags'];
        self::assertEquals(sizeof($tags), 10);
    }

    /** @test */
    public function testOneTagRouteResponse()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getStandardUser()), ['*']);
        for($i = 1; $i <= 10; $i++) {
            $resourcePath = url('/tags/' . $i);
            $requestPath = $resourcePath;
            $response = $this->get($requestPath);
            $response->assertStatus(200)->assertJsonStructure(['id', 'href', 'name', 'description']);
        }
    }
}
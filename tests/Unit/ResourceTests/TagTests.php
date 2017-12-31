<?php

namespace Tests\Unit;

use App\Tags\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class TagTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testTagResource()
    {
        $tag = Tag::getNutzungFremderInhalte();

        $resourcePath = $this->baseURI . $tag->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $tag->id,
            'name' => $tag->name,
            'description' => $tag->description
        ]);
    }

    /** @test */
    public function testTagCollection()
    {
        $tag1 = Tag::getNutzungFremderInhalte();
        $tag2 = Tag::getSozialeMedien();

        $resourcePath = $this->baseURI . '/tags';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'tags' => [
                [
                    //'href' => $this->baseURI . $tag1->getResourcePath(),
                    'id' => $tag1->id,
                    'name' => $tag1->name,
                    'description' => $tag1->description
                ],
                [
                    //'href' => $this->baseURI . $tag2->getResourcePath(),
                    'id' => $tag2->id,
                    'name' => $tag2->name,
                    'description' => $tag2->description
                ]
            ]
        ]);
    }
}

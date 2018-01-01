<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class ChangeTests extends TestCase
{
    use ResourceTestTrait;

    /**
     * @test */
    public function testChangeResource()
    {
        $subamendment = factory(SubAmendment::class)->states('user', 'amendment', 'accepted')->create()->fresh();

        $resourcePath = $this->baseURI . $subamendment->getChangesPath();

        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $subamendment->id,
            'handled_at' => $subamendment->handled_at,
            'updated_text' => []
        ]);

    }

    /**
     * @test */
    public function testChangeCollection()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $acceptedSubamendments = factory(SubAmendment::class, 5)->states('user', 'accepted')->create([
            'amendment_id' => $amendment->id
        ])->fresh();

        $resourcePath = $this->baseURI . $amendment->getResourcePath() . "/changes";

        $response = $this->get($resourcePath);

        $response->assertJson([
            'href' => $resourcePath,
            'changes' => $acceptedSubamendments->transform(function($subamendment){
                return [
                    'href' => $this->baseURI . $subamendment->getChangesPath(),
                    'id' => $subamendment->id,
                    'subamendment' => [
                        'href' => $this->baseURI . $subamendment->getResourcePath()
                    ]
                ];
            })->toArray()
        ]);
    }
}

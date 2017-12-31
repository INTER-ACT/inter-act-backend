<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Discussions\Discussion;
use App\Reports\Report;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class ReportTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testReportResource()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id
        ]);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $amendment->getIdProperty(),
            'reportable_type' => get_class($amendment),
            'explanation' => 'Test Description'
        ]);

        $resourcePath = $this->baseURI . $report->getResourcePath();
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $report->id,

            'user' => [
                'href' => $this->baseURI . $report->user->getResourcePath(),
                'id' => $report->user->id
            ],
            'reported_item' => [
                'href' => $this->baseURI . $report->reportable->getResourcePath(),
                'id' => $report->reportable->id,
                'type' => get_class($report->reportable)
            ],

            'description' => $report->explanation
        ]);
    }

    /** @test */
    public function testReportCollection()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id
        ]);
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id
        ]);
        $subamendment = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendment->id
        ]);
        $report1 = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $amendment->getIdProperty(),
            'reportable_type' => get_class($amendment),
            'explanation' => 'Test Description 1'
        ]);
        $report2 = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $subamendment->getIdProperty(),
            'reportable_type' => $subamendment->getType(),
            'explanation' => 'Test Description 2'
        ]);

        $resourcePath = $this->baseURI . '/reports';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'reports' => [
                [
                    'href' => $this->baseURI . $report1->getResourcePath(),
                    'id' => $report1->id
                ],
                [
                    'href' => $this->baseURI . $report2->getResourcePath(),
                    'id' => $report2->id
                ]
            ]
        ]);
    }
}

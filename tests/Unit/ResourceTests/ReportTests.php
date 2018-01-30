<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Discussions\Discussion;
use App\Model\ModelFactory;
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
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $report = ModelFactory::CreateReport($user, $amendment);

        $resourcePath = $this->getUrl($report->getResourcePath());
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'id' => $report->id,

            'user' => [
                'href' => $this->getUrl($report->user->getResourcePath()),
                'id' => $report->user->id
            ],
            'reported_item' => [
                'href' => $this->getUrl($report->reportable->getResourcePath()),
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
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $subamendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $report1 = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $amendment->getId(),
            'reportable_type' => get_class($amendment),
            'explanation' => 'Test Description 1'
        ]);
        $report2 = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $subamendment->getId(),
            'reportable_type' => $subamendment->getType(),
            'explanation' => 'Test Description 2'
        ]);

        $resourcePath = $this->baseURI . '/reports';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'reports' => [
                [
                    'href' => $this->getUrl($report1->getResourcePath()),
                    'id' => $report1->id
                ],
                [
                    'href' => $this->getUrl($report2->getResourcePath()),
                    'id' => $report2->id
                ]
            ]
        ]);
    }
}

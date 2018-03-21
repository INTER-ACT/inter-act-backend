<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 30.01.18
 * Time: 16:49
 */

namespace Tests\Feature;


use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\PostResources\ReportResource;
use App\Model\ModelFactory;
use App\Reports\Report;
use App\Role;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Tests\FeatureTestCase;

class ReportTests extends FeatureTestCase
{
    //region get /reports
    //TODO? test pagination
    /** @test */
    public function testGetAllReportsValid()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $comment2 = ModelFactory::CreateComment($user, $amendment);
        $comment3 = ModelFactory::CreateComment($user, $sub_amendment);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $report2 = ModelFactory::CreateReport($user, $sub_amendment);
        $report3 = ModelFactory::CreateReport($user, $comment);
        $report4 = ModelFactory::CreateReport($user, $comment2);
        $report5 = ModelFactory::CreateReport($user, $comment3);
        $all_reports = [$report1, $report2, $report3, $report4, $report5];
        $all_reports = array_map(function(Report $item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id
            ];
        },$all_reports);
        $requestPath = $this->getUrl('/reports');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'href' => $requestPath,
                    'reports' => $all_reports
                ]
            ]);
    }

    /** @test */
    public function testGetAllReportsValidWithTypeAmendments()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $comment2 = ModelFactory::CreateComment($user, $amendment);
        $comment3 = ModelFactory::CreateComment($user, $sub_amendment);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $report2 = ModelFactory::CreateReport($user, $sub_amendment);
        $report3 = ModelFactory::CreateReport($user, $comment);
        $report4 = ModelFactory::CreateReport($user, $comment2);
        $report5 = ModelFactory::CreateReport($user, $comment3);
        $all_reports = [$report1];
        $all_reports = array_map(function(Report $item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id
            ];
        },$all_reports);
        $requestPath = $this->getUrl('/reports?type=amendments');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'href' => $requestPath,
                    'reports' => $all_reports
                ]
            ]);
    }

    /** @test */
    public function testGetAllReportsValidWithTypeSubAmendments()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $comment2 = ModelFactory::CreateComment($user, $amendment);
        $comment3 = ModelFactory::CreateComment($user, $sub_amendment);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $report2 = ModelFactory::CreateReport($user, $sub_amendment);
        $report3 = ModelFactory::CreateReport($user, $comment);
        $report4 = ModelFactory::CreateReport($user, $comment2);
        $report5 = ModelFactory::CreateReport($user, $comment3);
        $all_reports = [$report2];
        $all_reports = array_map(function(Report $item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id
            ];
        },$all_reports);
        $requestPath = $this->getUrl('/reports?type=subamendments');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'href' => $requestPath,
                    'reports' => $all_reports
                ]
            ]);
    }

    /** @test */
    public function testGetAllReportsValidWithTypeComments()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $comment2 = ModelFactory::CreateComment($user, $amendment);
        $comment3 = ModelFactory::CreateComment($user, $sub_amendment);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $report2 = ModelFactory::CreateReport($user, $sub_amendment);
        $report3 = ModelFactory::CreateReport($user, $comment);
        $report4 = ModelFactory::CreateReport($user, $comment2);
        $report5 = ModelFactory::CreateReport($user, $comment3);
        $all_reports = [$report3, $report4, $report5];
        $all_reports = array_map(function(Report $item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id
            ];
        },$all_reports);
        $requestPath = $this->getUrl('/reports?type=comments');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'href' => $requestPath,
                    'reports' => $all_reports
                ]
            ]);
    }

    /** @test */
    public function testGetAllReportsValidFromOneUser()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $comment2 = ModelFactory::CreateComment($user, $amendment);
        $comment3 = ModelFactory::CreateComment($user, $sub_amendment);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $report2 = ModelFactory::CreateReport($user, $sub_amendment);
        $report3 = ModelFactory::CreateReport($user, $comment);
        $report4 = ModelFactory::CreateReport($user, $comment2);
        $report5 = ModelFactory::CreateReport($user, $comment3);

        $user2 = ModelFactory::CreateUser(Role::getStandardUser());
        $amendment2 = ModelFactory::CreateAmendment($user2, $discussion);
        $report6 = ModelFactory::CreateReport($user2, $amendment2);

        $all_reports = [$report1, $report2, $report3, $report4, $report5];
        $all_reports = array_map(function(Report $item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id
            ];
        },$all_reports);
        $requestPath = $this->getUrl('/reports?user_id=' . $user->id);
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'href' => $requestPath,
                    'reports' => $all_reports
                ]
            ]);
    }

    /** @test */
    public function testGetAllReportsValidFromOneUserWithTypeAmendments()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment);
        $comment = ModelFactory::CreateComment($user, $discussion);
        $comment2 = ModelFactory::CreateComment($user, $amendment);
        $comment3 = ModelFactory::CreateComment($user, $sub_amendment);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $report2 = ModelFactory::CreateReport($user, $sub_amendment);
        $report3 = ModelFactory::CreateReport($user, $comment);
        $report4 = ModelFactory::CreateReport($user, $comment2);
        $report5 = ModelFactory::CreateReport($user, $comment3);

        $user2 = ModelFactory::CreateUser(Role::getStandardUser());
        $amendment2 = ModelFactory::CreateAmendment($user2, $discussion);
        $report6 = ModelFactory::CreateReport($user2, $amendment2);

        $all_reports = [$report1];
        $all_reports = array_map(function(Report $item){
            return [
                'href' => $this->getUrl($item->getResourcePath()),
                'id' => $item->id
            ];
        },$all_reports);
        $requestPath = $this->getUrl('/reports?type=amendments&user_id=' . $user->id);
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'href' => $requestPath,
                    'reports' => $all_reports
                ]
            ]);
    }

    /** @test */
    public function testGetAllReportsNotAuthenticated()
    {
        $requestPath = $this->getUrl('/reports');
        $response = $this->get($requestPath);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testGetAllReportsNotPermitted()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getExpert()));
        $requestPath = $this->getUrl('/reports');
        $response = $this->get($requestPath);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }
    //endregion

    //region get /reports/{id}
    /** @test */
    public function testGetOneReportValid()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $requestPath = $this->getUrl($report1->getResourcePath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson((new ReportResource($report1))->toArray(new Request()));
    }

    /** @test */
    public function testGetOneReportNonexistent()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $requestPath = $this->getUrl('/reports/2');
        $response = $this->get($requestPath);
        $response->assertStatus(ResourceNotFoundException::HTTP_CODE)->assertJson(['code' => ResourceNotFoundException::ERROR_CODE]);
    }

    /** @test */
    public function testGetOneReportNotAuthenticated()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $requestPath = $this->getUrl($report1->getResourcePath());
        $response = $this->get($requestPath);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }

    /** @test */
    public function testGetOneReportNotPermitted()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getExpert()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $report1 = ModelFactory::CreateReport($user, $amendment);
        $requestPath = $this->getUrl($report1->getResourcePath());
        $response = $this->get($requestPath);
        $response->assertStatus(NotPermittedException::HTTP_CODE)->assertJson(['code' => NotPermittedException::ERROR_CODE]);
    }
    //endregion

    //region post /reports
    /** @test */
    public function testPostReportsValid()
    {
        $description = 'some report description';
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $inputData = [
            'reported_type' => $amendment->getApiFriendlyType(),
            'reportable_id' => $amendment->id,
            'description' => $description
        ];
        $requestPath = $this->getUrl('/reports');
        $response = $this->json('POST', $requestPath, $inputData);
        $newUrl = $this->getUrl(('/reports/' . 1));
        $response->assertStatus(201)
            ->assertJson([
                'href' => $newUrl,
                'id' => 1
            ]);
        $getData = $this->get($newUrl);
        $getData->assertJson([
            'href' => $newUrl,
            'id' => 1,
            'description' => $description
        ]);
    }

    /** @test */
    public function testPostReportsInvalidId()
    {
        $description = 'some report description';
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $inputData = [
            'reported_type' => $amendment->getApiFriendlyType(),
            'reportable_id' => 2,
            'description' => $description
        ];
        $requestPath = $this->getUrl('/reports');
        $response = $this->json('POST', $requestPath, $inputData);
        $newUrl = $this->getUrl(('/reports/' . 1));
        $response->assertStatus(CannotResolveDependenciesException::HTTP_CODE)->assertJson(['code' => CannotResolveDependenciesException::ERROR_CODE]);
    }

    /** @test */
    public function testPostReportsInvalidType()
    {
        $description = 'some report description';
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $inputData = [
            'reported_type' => 'emendment',
            'reportable_id' => $amendment->id,
            'description' => $description
        ];
        $requestPath = $this->getUrl('/reports');
        $response = $this->json('POST', $requestPath, $inputData);
        $newUrl = $this->getUrl(('/reports/' . 1));
        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
    }

    /** @test */
    public function testPostReportsTwiceSamePost()
    {
        $description = 'some report description';
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()));
        $user = \Auth::user();
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $inputData = [
            'reported_type' => $amendment->getApiFriendlyType(),
            'reportable_id' => $amendment->id,
            'description' => $description
        ];
        $requestPath = $this->getUrl('/reports');
        $response = $this->json('POST', $requestPath, $inputData);
        $newUrl = $this->getUrl(('/reports/' . 1));
        $response->assertStatus(201)
            ->assertJson([
                'href' => $newUrl,
                'id' => 1
            ]);
        $getData = $this->get($newUrl);
        $getData->assertJson([
            'href' => $newUrl,
            'id' => 1,
            'description' => $description
        ]);
        $description2 = 'some other report description';
        $inputData = [
            'reported_type' => $amendment->getApiFriendlyType(),
            'reportable_id' => $amendment->id,
            'description' => $description2
        ];
        $requestPath = $this->getUrl('/reports');
        $response = $this->json('POST', $requestPath, $inputData);
        $newUrl = $this->getUrl(('/reports/' . 1));
        $response->assertStatus(201)
            ->assertJson([
                'href' => $newUrl,
                'id' => 1
            ]);
        $getData = $this->get($newUrl);
        $getData->assertJson([
            'href' => $newUrl,
            'id' => 1,
            'description' => $description2
        ]);
    }

    /** @test */
    public function testPostReportsNotAuthenticated()
    {
        $description = 'some report description';
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user);
        $amendment = ModelFactory::CreateAmendment($user, $discussion);
        $inputData = [
            'reported_type' => $amendment->getApiFriendlyType(),
            'reportable_id' => 2,
            'description' => $description
        ];
        $requestPath = $this->getUrl('/reports');
        $response = $this->json('POST', $requestPath, $inputData);
        $response->assertStatus(NotAuthorizedException::HTTP_CODE)->assertJson(['code' => NotAuthorizedException::ERROR_CODE]);
    }
    //endregion
}
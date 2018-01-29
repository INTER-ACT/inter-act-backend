<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 29.01.18
 * Time: 09:04
 */

namespace Tests\Unit;

use App\Amendments\SubAmendment;
use App\CommentRating;
use App\Domain\ActionRepository;
use App\Model\ModelFactory;
use App\Role;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class StatisticalMethodTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testQuartileFunction()
    {
        $repo = new ActionRepository();
        $array = [6, 7, 15, 36, 39, 40, 41, 42, 43, 47, 49];
        $q = $repo->getQuartiles($array);
        $this->assertEquals(15, $q[0]);
        $this->assertEquals(40, $q[1]);
        $this->assertEquals(43, $q[2]);
        $array = [7, 15, 36, 39, 40, 41];
        $q = $repo->getQuartiles($array);
        $this->assertEquals(15, $q[0]);
        $this->assertEquals(37.5, $q[1]);
        $this->assertEquals(40, $q[2]);
    }

    /** @test */
    public function testCommentRatingStatistics()
    {
        $repo = new ActionRepository();
        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getStandardUser());
        $user3 = ModelFactory::CreateUser(Role::getScientist());
        $user4 = ModelFactory::CreateUser(Role::getExpert());
        $user5 = ModelFactory::CreateUser(Role::getStandardUser());
        $user6 = ModelFactory::CreateUser(Role::getStandardUser());
        $user7 = ModelFactory::CreateUser(Role::getStandardUser());
        $now = now();
        $discussion = ModelFactory::CreateDiscussion($user1);
        $comment = ModelFactory::CreateComment($user1, $discussion, [], Carbon::createFromDate(2017, 10, 10));
        $comment2 = ModelFactory::CreateComment($user1, $discussion, [], Carbon::createFromDate(2017, 10, 9));
        $commentTooEarly = ModelFactory::CreateComment($user1, $discussion, [], Carbon::createFromDate(2016, 1, 1));
        $commentTooLate = ModelFactory::CreateComment($user1, $discussion, [], Carbon::createFromDate(2018, 1, 1));
        ModelFactory::CreateCommentRating($user1, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1);
        ModelFactory::CreateCommentRating($user3, $comment, 1);
        ModelFactory::CreateCommentRating($user4, $comment, 1);
        ModelFactory::CreateCommentRating($user5, $comment, -1);
        ModelFactory::CreateCommentRating($user6, $comment, -1);
        ModelFactory::CreateCommentRating($user7, $comment, -1);
        ModelFactory::CreateCommentRating($user1, $comment2, -1);
        $statistics = $repo->getCommentRatingStatisticsResource('2017-01-01', '2017-12-31');
        $data = $statistics->getData();
        $this->assertEquals(2, sizeof($data));

        $pos_user_ages = collect([$user1, $user2, $user3, $user4])->pluck('year_of_birth')->transform(function($item) use($now){
            return $now->year - $item;
        })->all();
        sort($pos_user_ages);
        $neg_user_ages = collect([$user5, $user6, $user7])->pluck('year_of_birth')->transform(function($item) use($now){
            return $now->year - $item;
        })->all();
        sort($neg_user_ages);
        $q_pos = $repo->getQuartiles($pos_user_ages);
        $q_neg = $repo->getQuartiles($neg_user_ages);
        foreach ($q_pos as $item) {
            $this->assertEquals(true, in_array($item, $data[0]));
        }
        foreach ($q_neg as $item) {
            $this->assertEquals(true, in_array($item, $data[0]));
        }

        $pos_user_ages = collect([])->pluck('year_of_birth')->transform(function($item) use($now){
            return $now->year - $item;
        })->all();
        sort($pos_user_ages);
        $neg_user_ages = collect([$user1])->pluck('year_of_birth')->transform(function($item) use($now){
            return $now->year - $item;
        })->all();
        sort($neg_user_ages);
        $q_pos = $repo->getQuartiles($pos_user_ages);
        $q_neg = $repo->getQuartiles($neg_user_ages);
        foreach ($q_pos as $item) {
            $this->assertEquals(true, in_array($item, $data[1]));
        }
        foreach ($q_neg as $item) {
            $this->assertEquals(true, in_array($item, $data[1]));
        }
    }

    /** @test */
    public function testGeneralActivityStatistics()
    {
        $repo = new ActionRepository();
        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $discussionValid = ModelFactory::CreateDiscussion($user1, now());
        $discussionInvalid = ModelFactory::CreateDiscussion($user1, null, [], Carbon::createFromDate(1999, 10, 10));
        $amendmentValid = ModelFactory::CreateAmendment($user1, $discussionInvalid, [], now()->subDays(1));
        $amendmentInvalid = ModelFactory::CreateAmendment($user1, $discussionValid, [], Carbon::createFromDate(1999, 10, 10));
        $subamendmentValid = ModelFactory::CreateSubAmendment($user1, $amendmentValid, [], SubAmendment::PENDING_STATUS, now()->subDays(2));
        $commentValid = ModelFactory::CreateComment($user1, $discussionValid, [], now()->subDays(3));
        $commentInvalid = ModelFactory::CreateComment($user1, $discussionValid, [], Carbon::createFromDate(1999, 10, 9));
        ModelFactory::CreateCommentRating($user1, $commentValid, 1);
        //$commentRatingValid = (new GeneralActivityStatisticsResourceData($commentValid->getApiFriendlyTypeGer(), $commentValid->created_at, $user1->getSex(), $user1->postal_code, $user1->job, $user1->graduation, $user1->getAge(), $commentValid->getResourcePath(), 1))->toArray();
        $commentRatingValid = new CommentRating();
        $ratingValid = ModelFactory::CreateMultiAspectRating($user1, $amendmentValid, now()->subDays(10));
        $ratingInvalid = ModelFactory::CreateMultiAspectRating($user1, $amendmentValid, Carbon::createFromDate(1999, 10, 10));
        $expected_data = [$discussionValid, $amendmentValid, $subamendmentValid, $commentValid, $ratingValid, $commentRatingValid];
        $statistics = $repo->getGeneralActivityStatistics('2017-01-01');
        $data = $statistics->getData();
        $this->assertEquals(sizeof($data), sizeof($expected_data));
        for($i = 0; $i < sizeof($data); $i++)
        {
            $this->assertEquals($expected_data[$i]->getApiFriendlyTypeGer(), $data[$i][1]);
        }
    }

    /** @test */
    public function testObjectActivityStatistics()
    {
        $repo = new ActionRepository();

        //1 discussion              0 thisMonth
        //3 amendments              1 thisMonth
        //3 subamendments           2 thisMonth
        //7 comments                4 thisMonth
        //3 sub-comments            2 thisMonth
        //3 sub-sub-comments        2 thisMonth
        //3 Comment-Ratings         2 thisMonth         1 for each comment-type
        //3 Multi-Aspect-Ratings    2 thisMonth         1 for each ratable-type
        $total = 26;
        $thisMonth = 15;
        $date = now()->subMonth();
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], Carbon::createFromDate(2016, 1, 1));
        $amendment1 = ModelFactory::CreateAmendment($user, $discussion, [], Carbon::createFromDate(2016, 1, 10));
        $amendment2 = ModelFactory::CreateAmendment($user, $discussion, [], Carbon::createFromDate(2016, 1, 12));
        $amendment3 = ModelFactory::CreateAmendment($user, $discussion, [], $date->addDay());
        $sub1 = ModelFactory::CreateSubAmendment($user, $amendment1, [], SubAmendment::PENDING_STATUS, Carbon::createFromDate(2016, 1, 14));
        $sub2 = ModelFactory::CreateSubAmendment($user, $amendment2, [], SubAmendment::PENDING_STATUS, $date->addDay());
        $sub3 = ModelFactory::CreateSubAmendment($user, $amendment3, [], SubAmendment::PENDING_STATUS, $date->addDay());
        $co1 = ModelFactory::CreateComment($user, $discussion, [], $date->addDay());
        $co2 = ModelFactory::CreateComment($user, $amendment1, [], Carbon::createFromDate(2016, 1, 16));
        $co3 = ModelFactory::CreateComment($user, $amendment2, [], $date->addDay());
        $co4 = ModelFactory::CreateComment($user, $amendment3, [], $date->addDay());
        $co5 = ModelFactory::CreateComment($user, $sub1, [], Carbon::createFromDate(2016, 1, 18));
        $co6 = ModelFactory::CreateComment($user, $sub2, [], $date->addDay());
        $co7 = ModelFactory::CreateComment($user, $sub3, [], Carbon::createFromDate(2016, 1, 20));
        $sub_co1 = ModelFactory::CreateComment($user, $co1, [], Carbon::createFromDate(2016, 1, 22));
        $sub_co2 = ModelFactory::CreateComment($user, $co3, [], $date->addDay());
        $sub_co3 = ModelFactory::CreateComment($user, $co7, [], $date->addDay());
        $sub_sub_co1 = ModelFactory::CreateComment($user, $sub_co1, [], $date->addDay());
        $sub_sub_co2 = ModelFactory::CreateComment($user, $sub_co2, [], $date->addDay());
        $sub_sub_co3 = ModelFactory::CreateComment($user, $sub_co3, [], Carbon::createFromDate(2016, 1, 24));
        ModelFactory::CreateCommentRating($user, $co4, 1, $date->addDay());
        ModelFactory::CreateCommentRating($user, $sub_co2, 1, Carbon::createFromDate(2016, 1, 26));
        ModelFactory::CreateCommentRating($user, $sub_sub_co1, 1, $date->addDay());
        ModelFactory::CreateMultiAspectRating($user, $discussion, $date->addDay());
        ModelFactory::CreateMultiAspectRating($user, $amendment2, $date->addDay());
        ModelFactory::CreateMultiAspectRating($user, $sub3, Carbon::createFromDate(2016, 1, 28));

        $statistics = $repo->getObjectActivityStatisticsResource();
        $data = $statistics->getData();
        $this->assertEquals($total, $data[0][2]);
        $this->assertEquals($thisMonth, $data[0][3]);
    }
}
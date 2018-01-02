<?php

namespace Tests\Unit;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Model\ModelFactory;
use App\Reports\Report;
use App\User;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class StatisticsTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testUserStatisticsResource()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $discussion_count = 3;
        $amendment_count = 100;
        $subamendment_count = 1;
        $comment_count = 0;
        $discussions = factory(Discussion::class, $discussion_count)->create(['user_id' => $user->id]);
        $amendments = factory(Amendment::class, $amendment_count)->create(['user_id' => $user->id, 'discussion_id' => $discussions[0]->id]);
        $subamendments = factory(SubAmendment::class, $subamendment_count)->create(['user_id' => $user->id, 'amendment_id' => $amendments[0]->id]);
        $comments = factory(Comment::class, $comment_count)->create(['user_id' => $user->id, 'commentable_id' => $subamendments[0]->id, 'commentable_type' => $subamendments[0]->getType()]);

        $resourcePath = $this->baseURI . $user->getResourcePath() . '/statistics';
        $response = $this->get($resourcePath);
        $response->assertJson([
            'href' => $resourcePath,
            'comments_count' => $comment_count,
            'amendments_count' => $amendment_count,
            'subamendments_count' => $subamendment_count
        ]);
    }

    //region /statistics
    /** @test */
    public function testStatisticsResourceWithOne()
    {
        $this->StatisticsResourceTest(1, 1, 1, 1, 1);
    }

    /** @test */
    public function testStatisticsResourceWithZero()
    {
        $this->StatisticsResourceTest(1, 0, 0, 0, 0);
    }

    /** @test */
    public function testStatisticsResourceWithMany()
    {
        $this->StatisticsResourceTest(5, 5, 5, 5, 5);
    }

    private function StatisticsResourceTest(int $user_count, int $discussion_count, int $amendment_count, int $sub_amendment_count, int $comment_count)
    {
        if($user_count == 0)
            throw new Exception("User Count is zero!");
        $comment_rating_count = $comment_count;
        $report_count = $comment_count;
        $ma_rating_count = $sub_amendment_count + $amendment_count;
        /** @var array $users */
        $users = factory(User::class, $user_count)->create();
        $this->be($users[0]);
        $discussions = ModelFactory::CreateDiscussions($discussion_count, $users[0]);
        if($discussion_count > 0) {
            $amendments = ModelFactory::CreateAmendments($amendment_count, $users[0], $discussions[0]);
            if ($amendment_count > 0) {
                $sub_amendments = ModelFactory::CreateSubAmendments($sub_amendment_count, $users[0], $amendments[0]);
                $aspects = ModelFactory::CreateRatingAspects([
                    'fair', 'unfair', 'zielführend', 'nicht zielführend', 'perfekt', 'kompliziert', 'benachteiligend', 'blödsinning', 'interessant', 'unwichtig'
                ]);

                /** @var Amendment $amendment */
                foreach ($amendments as $amendment)
                    $amendment->rating_aspects()->attach(array_map(function ($item) {
                        return $item->id;
                    }, $aspects));
                /** @var SubAmendment $subamendment */
                foreach ($sub_amendments as $subamendment)
                    $subamendment->rating_aspects()->attach(array_map(function ($item) {
                        return $item->id;
                    }, $aspects));
                foreach ($amendments as $amendment)
                    ModelFactory::CreateRating($users[0], $amendment, $aspects[0]);
                foreach ($sub_amendments as $sub_amendment)
                    ModelFactory::CreateRating($users[0], $sub_amendment, $aspects[0]);
                if($sub_amendment_count > 0) {
                    $comments = ModelFactory::CreateComments($comment_count, $users[0], $sub_amendments[0]);
                    foreach ($comments as $comment) {
                        ModelFactory::CreateCommentRating($users[0], $comment, 1);
                        ModelFactory::CreateReport($users[0], $comment);
                    }
                }
            }
        }
        $avg_age = DB::selectOne('SELECT AVG(age) as val from (SELECT (YEAR(CURRENT_DATE()) - year_of_birth) as age from users) as age_table')->val;
        $male_count = DB::selectOne('SELECT COUNT(id) as val from users WHERE is_male = 1')->val;
        $female_count = DB::selectOne('SELECT COUNT(id) as val from users WHERE is_male = 0')->val;

        $resourcePath = $this->baseURI . '/test/statistics';
        $response = $this->get($resourcePath);
        $response->assertJson([
            [
                'Anzahl Benutzer',
                'Durchschnittsalter Benutzer',
                'Anzahl männlicher Benutzer',
                'Anzahl weiblicher Benutzer',
                'Anzahl Diskussionen',
                'Anzahl Änderungsvorschläge',
                'Anzahl Sub-Änderungsvorschläge',
                'Anzahl Multiple-Aspect-Ratings',
                'Anzahl Kommentare',
                'Anzahl Kommentar-Bewertungen',
                'Anzahl Reports'
            ],
            [
                $user_count,
                $avg_age,
                $male_count,
                $female_count,
                $discussion_count,
                $amendment_count,
                $sub_amendment_count,
                $ma_rating_count,
                $comment_count,
                $comment_rating_count,
                $report_count
            ]
        ]);
    }
    //endregion

    /** @test */
    public function testDiscussionStatisticsResource()
    {
        self::assertEquals(true, true);
        //TODO: implement testDiscussionStatisticsResource()
    }
}

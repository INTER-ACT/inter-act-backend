<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.12.17
 * Time: 10:08
 */

namespace Tests\Unit;


use App\Amendments\SubAmendment;
use App\Model\ModelFactory;
use App\Role;
use App\Tags\Tag;
use Carbon\Carbon;
use PhpParser\Node\Expr\AssignOp\Mod;
use Tests\TestCase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class ModelFieldTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testCommentActivityAttribute()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], now()->subMonths(15));
        $comment = ModelFactory::CreateComment($user, $discussion, [], now()->subYear());
        ModelFactory::CreateCommentRating($user, $comment, 1);
        $this->assertEquals(2, $comment->getActivityAttribute());
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $this->assertEquals(3, $comment->getActivityAttribute());
        $this->assertEquals(1, $comment->getActivity(now()->subMonth(), now()));
        $sub = ModelFactory::CreateComment($user, $comment, [], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);
        $this->assertEquals(6, $comment->getActivityAttribute());
        $this->assertEquals(3, $comment->getActivity(now()->subMonth(), now()));
    }

    /** @test */
    public function testSubAmendmentActivityAttribute()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], now()->subMonths(15));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], now()->subMonths(12));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, [], SubAmendment::PENDING_STATUS, now()->subYear());
        $oldComment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(11));
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(3));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $sub = ModelFactory::CreateComment($user, $comment, [], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);
        $this->assertEquals(1 + $comment->getActivityAttribute() + $oldComment->getActivityAttribute(), $sub_amendment->getActivityAttribute());
        $start = now()->subMonth();
        $end = now();
        $this->assertEquals($comment->getActivity($start), $sub_amendment->getActivity($start, $end));
    }

    /** @test */
    public function testAmendmentActivityAttribute()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], now()->subMonths(15));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], now()->subMonths(13));
        $comment_am = ModelFactory::CreateComment($user, $amendment, [], now()->subMonths(3));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, [], SubAmendment::PENDING_STATUS, now()->subYear());
        $oldComment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(11));
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(3));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $sub = ModelFactory::CreateComment($user, $comment, [], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);
        $this->assertEquals(1 + $comment_am->getActivityAttribute() + $sub_amendment->getActivityAttribute(), $amendment->getActivityAttribute());
        $start = now()->subMonth();
        $end = now();
        $this->assertEquals($comment_am->getActivity($start) + $sub_amendment->getActivity($start), $sub_amendment->getActivity($start, $end));
    }

    /** @test */
    public function testDiscussionActivityAttribute()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], now()->subMonths(15));
        $comment_dis = ModelFactory::CreateComment($user, $discussion, [], now()->subMonths(1));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], now()->subMonths(13));
        $comment_am = ModelFactory::CreateComment($user, $amendment, [], now()->subMonths(3));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, [], SubAmendment::PENDING_STATUS, now()->subYear());
        $oldComment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(11));
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(3));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $sub = ModelFactory::CreateComment($user, $comment, [], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);
        $this->assertEquals(1 + $comment_dis->getActivityAttribute() + $amendment->getActivityAttribute(), $discussion->getActivityAttribute());
        $start = now()->subMonth();
        $end = now();
        $this->assertEquals($comment_dis->getActivity($start) + $amendment->getActivity($start), $discussion->getActivity($start, $end));
    }

    /** @test */
    public function testTagActivityAttribute()
    {
        $tag = Tag::getUserGeneratedContent();
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [$tag], now()->subMonths(15));
        $comment_dis = ModelFactory::CreateComment($user, $discussion, [], now()->subMonths(1));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [], now()->subMonths(13));
        $comment_am = ModelFactory::CreateComment($user, $amendment, [], now()->subMonths(3));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, [], SubAmendment::PENDING_STATUS, now()->subYear());
        $oldComment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(11));
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [], now()->subMonths(3));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $sub = ModelFactory::CreateComment($user, $comment, [], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);

        $this->assertEquals($discussion->getActivityAttribute(), $tag->getActivityAttribute());
        $start = now()->subMonth();
        $end = now();
        $this->assertEquals($discussion->getActivity($start), $tag->getActivity($start, $end));
    }

    /** @test */
    public function testTagActivityAttributeAllTagged()
    {
        $tag = Tag::getUserGeneratedContent();
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [$tag], now()->subMonths(15));
        $comment_dis = ModelFactory::CreateComment($user, $discussion, [$tag], now()->subMonths(1));
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [$tag], now()->subMonths(13));
        $comment_am = ModelFactory::CreateComment($user, $amendment, [$tag], now()->subMonths(3));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, [$tag], SubAmendment::PENDING_STATUS, now()->subYear());
        $oldComment = ModelFactory::CreateComment($user, $sub_amendment, [$tag], now()->subMonths(11));
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [$tag], now()->subMonths(3));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $sub = ModelFactory::CreateComment($user, $comment, [$tag], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);

        $this->assertEquals($discussion->getActivityAttribute(), $tag->getActivityAttribute());
        $start = now()->subMonth();
        $end = now();
        $this->assertEquals($discussion->getActivity($start), $tag->getActivity($start, $end));
    }

    /** @test */
    public function testTagActivityAttributeExtended()
    {
        $tag = Tag::getUserGeneratedContent();
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user, null, [], now()->subMonths(15));
        $comment_dis = ModelFactory::CreateComment($user, $discussion, [$tag], now()->subWeek());
        $amendment = ModelFactory::CreateAmendment($user, $discussion, [$tag], now()->subMonths(13));
        $comment_am = ModelFactory::CreateComment($user, $amendment, [$tag], now()->subMonths(3));
        $sub_amendment = ModelFactory::CreateSubAmendment($user, $amendment, [$tag], SubAmendment::PENDING_STATUS, now()->subYear());
        $oldComment = ModelFactory::CreateComment($user, $sub_amendment, [$tag], now()->subMonths(11));
        $comment = ModelFactory::CreateComment($user, $sub_amendment, [$tag], now()->subMonths(3));
        ModelFactory::CreateCommentRating($user, $comment, 1);
        ModelFactory::CreateCommentRating($user2, $comment, 1, now()->subMonths(2));
        $sub = ModelFactory::CreateComment($user, $comment, [$tag], now()->subMonths(2));
        ModelFactory::CreateCommentRating($user, $sub, -1);
        ModelFactory::CreateCommentRating($user2, $sub, 1);

        $discussion2 = ModelFactory::CreateDiscussion($user, null, [], now()->subMonths(15));
        $amendment2 = ModelFactory::CreateAmendment($user, $discussion2, [], now()->subMonths(13));
        $sub_amendment2 = ModelFactory::CreateSubAmendment($user, $amendment2, [$tag], SubAmendment::PENDING_STATUS, now()->subYear());
        $this->assertEquals($comment_dis->activity + $amendment->activity + $sub_amendment2->activity, $tag->activity);
        $start = now()->subMonth();
        $end = now();
        $this->assertEquals($comment_dis->getActivity($start) + $amendment->getActivity($start) + $sub_amendment2->getActivity($start), $tag->getActivity($start, $end));
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.01.18
 * Time: 14:50
 */

namespace Tests\Unit;


use App\Domain\DiscussionRepository;
use App\Domain\PageRequest;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Model\ModelFactory;
use App\Role;
use Tests\TestCase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class SortingMethodTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testSortDiscussionsByPopularityRatings()
    {
        $repository = new DiscussionRepository();

        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getStandardUser());

        $discussion1 = ModelFactory::CreateDiscussion($user1);
        $discussion2 = ModelFactory::CreateDiscussion($user1);
        $discussion3 = ModelFactory::CreateDiscussion($user1);

        ModelFactory::CreateMultiAspectRating($user1, $discussion2);
        ModelFactory::CreateMultiAspectRating($user2, $discussion2);
        ModelFactory::CreateMultiAspectRating($user1, $discussion3);
        $discussions = new DiscussionCollection(collect([$discussion2, $discussion3, $discussion1]));
        $sorted_discussions = $repository->getAll(new PageRequest(), 'popularity');
        $this->assertEquals($discussions->resolve(), $sorted_discussions->resolve());
    }

    /** @test */
    public function testSortDiscussionsByPopularityAmendments()
    {
        $repository = new DiscussionRepository();

        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getStandardUser());

        $discussion1 = ModelFactory::CreateDiscussion($user1);
        $discussion2 = ModelFactory::CreateDiscussion($user1);
        $discussion3 = ModelFactory::CreateDiscussion($user1);

        ModelFactory::CreateAmendment($user1, $discussion2);
        ModelFactory::CreateAmendment($user1, $discussion2);
        ModelFactory::CreateAmendment($user1, $discussion3);

        $discussions = new DiscussionCollection(collect([$discussion2, $discussion3, $discussion1]));
        $sorted_discussions = $repository->getAll(new PageRequest(), 'popularity');
        $this->assertEquals($discussions->resolve(), $sorted_discussions->resolve());
    }

    /** @test */
    public function testSortDiscussionsByPopularitySubAmendments()
    {
        $repository = new DiscussionRepository();

        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getStandardUser());

        $discussion1 = ModelFactory::CreateDiscussion($user1);
        $discussion2 = ModelFactory::CreateDiscussion($user1);
        $discussion3 = ModelFactory::CreateDiscussion($user1);

        $amendment1 = ModelFactory::CreateAmendment($user1, $discussion1);
        $amendment2 = ModelFactory::CreateAmendment($user1, $discussion2);
        $amendment3 = ModelFactory::CreateAmendment($user1, $discussion3);

        ModelFactory::CreateSubAmendment($user1, $amendment2);
        ModelFactory::CreateSubAmendment($user1, $amendment2);
        ModelFactory::CreateSubAmendment($user1, $amendment3);

        $discussions = new DiscussionCollection(collect([$discussion2, $discussion3, $discussion1]));
        $sorted_discussions = $repository->getAll(new PageRequest(), 'popularity');
        $this->assertEquals($discussions->resolve(), $sorted_discussions->resolve());
    }

    /** @test */
    public function testSortDiscussionsByPopularityComments()
    {
        $repository = new DiscussionRepository();

        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getStandardUser());

        $discussion1 = ModelFactory::CreateDiscussion($user1);
        $discussion2 = ModelFactory::CreateDiscussion($user1);
        $discussion3 = ModelFactory::CreateDiscussion($user1);

        $amendment1 = ModelFactory::CreateAmendment($user1, $discussion1);
        $amendment2 = ModelFactory::CreateAmendment($user1, $discussion2);
        $amendment3 = ModelFactory::CreateAmendment($user1, $discussion3);

        $subamendment1 = ModelFactory::CreateSubAmendment($user1, $amendment1);
        $subamendment2 = ModelFactory::CreateSubAmendment($user1, $amendment2);
        $subamendment3 = ModelFactory::CreateSubAmendment($user1, $amendment3);

        ModelFactory::CreateComment($user1, $discussion1);
        ModelFactory::CreateComment($user1, $amendment2);
        ModelFactory::CreateComment($user1, $amendment2);
        ModelFactory::CreateComment($user1, $amendment2);
        ModelFactory::CreateComment($user1, $subamendment3);
        ModelFactory::CreateComment($user1, $subamendment3);

        $discussions = new DiscussionCollection(collect([$discussion2, $discussion3, $discussion1]));
        $sorted_discussions = $repository->getAll(new PageRequest(), 'popularity');
        $this->assertEquals($discussions->resolve(), $sorted_discussions->resolve());
    }

    /** @test */
    public function testSortDiscussionsByPopularityCommentRatings()
    {
        $repository = new DiscussionRepository();

        $user1 = ModelFactory::CreateUser(Role::getAdmin());
        $user2 = ModelFactory::CreateUser(Role::getStandardUser());
        $user3 = ModelFactory::CreateUser(Role::getStandardUser());

        $discussion1 = ModelFactory::CreateDiscussion($user1);
        $discussion2 = ModelFactory::CreateDiscussion($user1);
        $discussion3 = ModelFactory::CreateDiscussion($user1);

        $amendment1 = ModelFactory::CreateAmendment($user1, $discussion1);
        $amendment2 = ModelFactory::CreateAmendment($user1, $discussion2);
        $amendment3 = ModelFactory::CreateAmendment($user1, $discussion3);

        $subamendment1 = ModelFactory::CreateSubAmendment($user1, $amendment1);
        $subamendment2 = ModelFactory::CreateSubAmendment($user1, $amendment2);
        $subamendment3 = ModelFactory::CreateSubAmendment($user1, $amendment3);

        $comment1 = ModelFactory::CreateComment($user1, $discussion1);
        $comment2 = ModelFactory::CreateComment($user1, $amendment2);
        $comment3 = ModelFactory::CreateComment($user1, $amendment2);
        $comment4 = ModelFactory::CreateComment($user1, $amendment2);
        $comment5 = ModelFactory::CreateComment($user1, $subamendment3);
        $comment6 = ModelFactory::CreateComment($user1, $subamendment3);

        ModelFactory::CreateCommentRating($user1, $comment1, 1);
        ModelFactory::CreateCommentRating($user2, $comment1, 1);
        ModelFactory::CreateCommentRating($user3, $comment1, 1);

        $discussions = new DiscussionCollection(collect([$discussion1, $discussion2, $discussion3]));
        $sorted_discussions = $repository->getAll(new PageRequest(), 'popularity');
        $sorted_array = $sorted_discussions->resolve();
        $this->assertEquals($discussions->resolve(), $sorted_array);
        $discussions = new DiscussionCollection(collect([$discussion2, $discussion3, $discussion1]));
        $this->assertNotEquals($discussions->resolve(), $sorted_array);
    }
}
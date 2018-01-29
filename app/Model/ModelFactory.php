<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 02.01.18
 * Time: 09:46
 */

namespace App\Model;

use App\Amendments\Amendment;
use App\Amendments\IRatable;
use App\Amendments\RatableRatingAspect;
use App\Amendments\RatingAspect;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Discussions\Discussion;
use App\MultiAspectRating;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Role;
use App\User;
use Carbon\Carbon;
use DateTime;

class ModelFactory
{
    /**
     * @param Role $role
     * @return User
     */
    public static function CreateUser(Role $role)
    {
        return factory(User::class)->create([
            'role_id' => $role->id
        ]);
    }

    /**
     * @param int $discussionCount
     * @param User $user
     * @param DateTime|null $archived_at
     * @param array|null $tags
     * @return array
     */
    public static function CreateDiscussions(int $discussionCount, User $user, DateTime $archived_at = null, array $tags = null)
    {
        $discussions = [];
        for($i = 0; $i < $discussionCount; $i++)
        {
            array_push($discussions, self::CreateDiscussion($user, $archived_at, $tags));
        }
        return $discussions;
    }

    /**
     * @param User $user
     * @param DateTime|null $archived_at
     * @param array|null $tags
     * @param Carbon|null $created_at
     *
     * @return Discussion
     */
    public static function CreateDiscussion(User $user, DateTime $archived_at = null, array $tags = null, Carbon $created_at = null)
    {
        $created_at = ($created_at === null) ? now() : $created_at;
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'archived_at' => $archived_at,
            'created_at' => $created_at
        ]);
        if($tags and isset($tags))
            $discussion->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $discussion;
    }

    /**
     * @param int $amendmentCount
     * @param User $user
     * @param Discussion $discussion
     * @param array|null $tags
     * @return array
     */
    public static function CreateAmendments(int $amendmentCount, User $user, Discussion $discussion, array $tags = null)
    {
        $amendments = [];
        for($i = 0; $i < $amendmentCount; $i++)
        {
            array_push($amendments, self::CreateAmendment($user, $discussion, $tags));
        }
        return $amendments;
    }

    /**
     * @param User $user
     * @param Discussion $discussion
     * @param array|null $tags
     * @param Carbon|null $created_at
     * @return Amendment
     */
    public static function CreateAmendment(User $user, Discussion $discussion, array $tags = null, Carbon $created_at = null)
    {
        if($created_at == null)
            $created_at = now();
        /** @var Amendment $amendment */
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id,
            'created_at' => $created_at
        ]);
        if(isset($tags))
            $amendment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $amendment;
    }

    /**
     * @param int $subAmendmentCount
     * @param User $user
     * @param Amendment $amendment
     * @param array|null $tags
     * @param string $status
     * @return array
     */
    public static function CreateSubAmendments(int $subAmendmentCount, User $user, Amendment $amendment, array $tags = null, string $status = SubAmendment::PENDING_STATUS)
    {
        $sub_amendments = [];
        for($i = 0; $i < $subAmendmentCount; $i++)
        {
            array_push($sub_amendments, self::CreateSubAmendment($user, $amendment, $tags, $status));
        }
        return $sub_amendments;
    }

    /**
     * @param User $user
     * @param Amendment $amendment
     * @param array|null $tags
     * @param string $status
     * @param Carbon|null $created_at
     * @return SubAmendment
     */
    public static function CreateSubAmendment(User $user, Amendment $amendment, array $tags = null, string $status = SubAmendment::PENDING_STATUS, Carbon $created_at = null)
    {
        $created_at = (isset($created_at)) ? $created_at : now();
        /** @var SubAmendment $subAmendment */
        $subAmendment = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendment->id,
            'status' => $status,
            'created_at' => $created_at
        ]);
        if(isset($tags))
            $subAmendment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $subAmendment;
    }

    /**
     * @param User $user
     * @param IRatable $ratable
     * @param Carbon|null $created_at
     * @return MultiAspectRating
     */
    public static function CreateMultiAspectRating(User $user, IRatable $ratable, Carbon $created_at = null) : MultiAspectRating
    {
        if(!isset($created_at))
            $created_at = now();
        $rating = factory(MultiAspectRating::class)->create([
            'user_id' => $user->id,
            'ratable_id' => $ratable->getId(),
            'ratable_type' => $ratable->getType(),
            'created_at' => $created_at
        ]);
        return $rating;
    }

    /**
     * @param int $commentCount
     * @param User $user
     * @param ICommentable $parent
     * @param array|null $tags
     * @return array
     */
    public static function CreateComments(int $commentCount, User $user, ICommentable $parent, array $tags = null)
    {
        $comments = [];
        for($i = 0; $i < $commentCount; $i++)
        {
            array_push($comments, self::CreateComment($user, $parent, $tags));
        }
        return $comments;
    }

    /**
     * @param User $user
     * @param \App\Comments\ICommentable $parent
     * @param array|null $tags
     * @param Carbon|null $created_at
     * @return Comment
     */
    public static function CreateComment(User $user, ICommentable $parent, array $tags = null, Carbon $created_at = null)
    {
        if(!isset($created_at))
            $created_at = now();
        /** @var Comment $comment */
        $comment = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $parent->getId(),
            'commentable_type' => get_class($parent),
            'created_at' => $created_at
        ]);
        if(isset($tags))
            $comment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $comment;
    }

    /**
     * @param User $user
     * @param Comment $comment
     * @param int $rating
     * @param Carbon|null $created_at
     * @return void
     */
    public static function CreateCommentRating(User $user, Comment $comment, int $rating, Carbon $created_at = null)
    {
        if(!isset($created_at))
            $created_at = now();
        $comment->rating_users()->attach($user->id, ['rating_score' => $rating, 'created_at' => $created_at]);
    }

    /**
     * @param User $user
     * @param IReportable $reportable
     * @return Report
     */
    public static function CreateReport(User $user, IReportable $reportable)
    {
        /** @var Report $report */
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $reportable->getId(),
            'reportable_type' => get_class($reportable)
        ]);
        return $report;
    }
}
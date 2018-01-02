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
use App\Reports\IReportable;
use App\Reports\Report;
use App\Role;
use App\User;
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
     * @return Discussion
     */
    public static function CreateDiscussion(User $user, DateTime $archived_at = null, array $tags = null)
    {
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'archived_at' => $archived_at
        ]);
        if($tags and isset($tags))
            $discussion->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $discussion;
    }

    /**
     * @param array $aspect_names
     * @return array
     */
    public static function CreateRatingAspects(array $aspect_names)
    {
        $aspects = [];
        foreach ($aspect_names as $aspect_name)
        {
            array_push($aspects, self::CreateRatingAspect($aspect_name));
        }
        return $aspects;
    }

    /**
     * @param string $name
     * @return RatingAspect
     */
    public static function CreateRatingAspect(string $name)
    {
        return RatingAspect::create(['name' => $name]);
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
     * @return Amendment
     */
    public static function CreateAmendment(User $user, Discussion $discussion, array $tags = null)
    {
        /** @var Amendment $amendment */
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id
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
     * @return SubAmendment
     */
    public static function CreateSubAmendment(User $user, Amendment $amendment, array $tags = null, string $status = SubAmendment::PENDING_STATUS)  //TODO: make sure that status cannot be wrong (enum or so)
    {
        /** @var SubAmendment $subAmendment */
        $subAmendment = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendment->id,
            'status' => $status
        ]);
        if(isset($tags))
            $subAmendment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $subAmendment;
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
     * @return Comment
     */
    public static function CreateComment(User $user, ICommentable $parent, array $tags = null)
    {
        /** @var Comment $comment */
        $comment = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $parent->getIdProperty(),
            'commentable_type' => get_class($parent)
        ]);
        if(isset($tags))
            $comment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $comment;
    }

    /**
     * @param User $user
     * @param Comment $comment
     * @param int $rating
     * @return void
     */
    public static function CreateCommentRating(User $user, Comment $comment, int $rating)
    {
        $comment->rating_users()->attach($user->id, ['rating_score' => $rating]);
    }

    /**
     * @param User $user
     * @param IRatable $ratable
     * @param RatingAspect $ratingAspect
     * @return RatableRatingAspect|null
     */
    public static function CreateRating(User $user, IRatable $ratable, RatingAspect $ratingAspect)
    {
        /** @var RatableRatingAspect $rating */
        $rating = RatableRatingAspect::where([['ratable_id', '=', $ratable->getIdProperty()], ['ratable_type', '=', get_class($ratable)], ['rating_aspect_id', '=', $ratingAspect->id]])->first();
        if($rating === null)
            return null;
        $rating->user_ratings()->attach($user->id);
        return $rating;
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
            'reportable_id' => $reportable->getIdProperty(),
            'reportable_type' => get_class($reportable)
        ]);
        return $report;
    }
}
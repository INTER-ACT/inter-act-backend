<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 02.01.18
 * Time: 08:59
 */

namespace App\Http\Resources\StatisticsResources;


class StatisticsResourceData
{
    /** @var int */
    protected $user_count;
    /** @var float */
    protected $avg_user_age;
    /** @var int */
    protected $male_user_count;
    /** @var int */
    protected $female_user_count;
    /** @var int */
    protected $discussion_count;
    /** @var int */
    protected $amendment_count;
    /** @var int */
    protected $sub_amendment_count;
    /** @var int */
    protected $ma_rating_count;
    /** @var int */
    protected $comment_count;
    /** @var int */
    protected $comment_rating_count;
    /** @var int */
    protected $report_count;

    /**
     * StatisticsResourceData constructor.
     * @param int $user_count
     * @param float $avg_user_age
     * @param int $male_user_count
     * @param int $female_user_count
     * @param int $discussion_count
     * @param int $amendment_count
     * @param int $sub_amendment_count
     * @param int $ma_rating_count
     * @param int $comment_count
     * @param int $comment_rating_count
     * @param int $report_count
     */
    public function __construct(int $user_count, float $avg_user_age, int $male_user_count, int $female_user_count, int $discussion_count, int $amendment_count, int $sub_amendment_count, int $ma_rating_count, int $comment_count, int $comment_rating_count, int $report_count)
    {
        $this->user_count = $user_count;
        $this->avg_user_age = $avg_user_age;
        $this->male_user_count = $male_user_count;
        $this->female_user_count = $female_user_count;
        $this->discussion_count = $discussion_count;
        $this->amendment_count = $amendment_count;
        $this->sub_amendment_count = $sub_amendment_count;
        $this->ma_rating_count = $ma_rating_count;
        $this->comment_count = $comment_count;
        $this->comment_rating_count = $comment_rating_count;
        $this->report_count = $report_count;
    }

    //region Getters and Setters
    /**
     * @return int
     */
    public function getUserCount(): int
    {
        return $this->user_count;
    }

    /**
     * @param int $user_count
     */
    public function setUserCount(int $user_count)
    {
        $this->user_count = $user_count;
    }

    /**
     * @return float
     */
    public function getAvgUserAge(): float
    {
        return $this->avg_user_age;
    }

    /**
     * @param float $avg_user_age
     */
    public function setAvgUserAge(float $avg_user_age)
    {
        $this->avg_user_age = $avg_user_age;
    }

    /**
     * @return int
     */
    public function getMaleUserCount(): int
    {
        return $this->male_user_count;
    }

    /**
     * @param int $male_user_count
     */
    public function setMaleUserCount(int $male_user_count)
    {
        $this->male_user_count = $male_user_count;
    }

    /**
     * @return int
     */
    public function getFemaleUserCount(): int
    {
        return $this->female_user_count;
    }

    /**
     * @param int $female_user_count
     */
    public function setFemaleUserCount(int $female_user_count)
    {
        $this->female_user_count = $female_user_count;
    }

    /**
     * @return int
     */
    public function getDiscussionCount(): int
    {
        return $this->discussion_count;
    }

    /**
     * @param int $discussion_count
     */
    public function setDiscussionCount(int $discussion_count)
    {
        $this->discussion_count = $discussion_count;
    }

    /**
     * @return int
     */
    public function getAmendmentCount(): int
    {
        return $this->amendment_count;
    }

    /**
     * @param int $amendment_count
     */
    public function setAmendmentCount(int $amendment_count)
    {
        $this->amendment_count = $amendment_count;
    }

    /**
     * @return int
     */
    public function getSubAmendmentCount(): int
    {
        return $this->sub_amendment_count;
    }

    /**
     * @param int $sub_amendment_count
     */
    public function setSubAmendmentCount(int $sub_amendment_count)
    {
        $this->sub_amendment_count = $sub_amendment_count;
    }

    /**
     * @return int
     */
    public function getMaRatingCount(): int
    {
        return $this->ma_rating_count;
    }

    /**
     * @param int $ma_rating_count
     */
    public function setMaRatingCount(int $ma_rating_count)
    {
        $this->ma_rating_count = $ma_rating_count;
    }

    /**
     * @return int
     */
    public function getCommentCount(): int
    {
        return $this->comment_count;
    }

    /**
     * @param int $comment_count
     */
    public function setCommentCount(int $comment_count)
    {
        $this->comment_count = $comment_count;
    }

    /**
     * @return int
     */
    public function getCommentRatingCount(): int
    {
        return $this->comment_rating_count;
    }

    /**
     * @param int $comment_rating_count
     */
    public function setCommentRatingCount(int $comment_rating_count)
    {
        $this->comment_rating_count = $comment_rating_count;
    }

    /**
     * @return int
     */
    public function getReportCount(): int
    {
        return $this->report_count;
    }

    /**
     * @param int $report_count
     */
    public function setReportCount(int $report_count)
    {
        $this->report_count = $report_count;
    }
    //endregion
}
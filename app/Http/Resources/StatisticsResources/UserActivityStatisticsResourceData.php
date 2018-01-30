<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 09.01.18
 * Time: 08:12
 */

namespace App\Http\Resources\StatisticsResources;


class UserActivityStatisticsResourceData
{
    /** @var string */
    protected $user_path;
    /** @var string */
    protected $entity_path;
    /** @var string */
    protected $teaser;
    /** @var int */
    protected $activity_score;

    /**
     * UserActivityStatisticsResourceData constructor.
     * @param string $user_path
     * @param string $post_path
     * @param string $teaser
     * @param int $activity_score
     */
    public function __construct(string $user_path, string $post_path, string $teaser = null, int $activity_score)
    {
        $this->user_path = $user_path;
        $this->entity_path = $post_path;
        $this->teaser = $teaser;
        $this->activity_score = $activity_score;
    }

    //region Getters and Setters
    /**
     * @return string
     */
    public function getUserPath(): string
    {
        return $this->user_path;
    }

    /**
     * @param string $user_path
     */
    public function setUserPath(string $user_path)
    {
        $this->user_path = $user_path;
    }

    /**
     * @return string
     */
    public function getEntityPath(): string
    {
        return $this->entity_path;
    }

    /**
     * @param string $entity_path
     */
    public function setEntityPath(string $entity_path)
    {
        $this->entity_path = $entity_path;
    }

    /**
     * @return string
     */
    public function getTeaser(): string
    {
        return $this->teaser;
    }

    /**
     * @param string $teaser
     */
    public function setTeaser(string $teaser)
    {
        $this->teaser = $teaser;
    }

    /**
     * @return int
     */
    public function getActivityScore(): int
    {
        return $this->activity_score;
    }

    /**
     * @param int $activity_score
     */
    public function setActivityScore(int $activity_score)
    {
        $this->activity_score = $activity_score;
    }

    public function toArray()
    {
        return [
            $this->user_path,
            $this->entity_path,
            $this->teaser,
            $this->activity_score
        ];
    }
    //endregion
}
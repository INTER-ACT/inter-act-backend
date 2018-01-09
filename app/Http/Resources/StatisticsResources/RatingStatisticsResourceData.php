<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 09.01.18
 * Time: 08:08
 */

namespace App\Http\Resources\StatisticsResources;


class RatingStatisticsResourceData
{
    /** @var string */
    protected $date;
    /** @var string */
    protected $gender;
    /** @var int */
    protected $postal_code;
    /** @var string */
    protected $job;
    /** @var string */
    protected $highest_graduation;
    /** @var int */
    protected $age;
    /** @var string */
    protected $ratable_path;
    /** @var string */
    protected $rating_aspect_name;

    /**
     * RatingStatisticsResourceData constructor.
     * @param string $date
     * @param string $gender
     * @param int $postal_code
     * @param string $job
     * @param string $highest_graduation
     * @param int $age
     * @param string $ratable_path
     * @param string $rating_aspect_name
     */
    public function __construct($date, $gender, $postal_code, $job, $highest_graduation, $age, $ratable_path, $rating_aspect_name)
    {
        $this->date = $date;
        $this->gender = $gender;
        $this->postal_code = $postal_code;
        $this->job = $job;
        $this->highest_graduation = $highest_graduation;
        $this->age = $age;
        $this->ratable_path = $ratable_path;
        $this->rating_aspect_name = $rating_aspect_name;
    }

    //region Getters and Setters
    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender(string $gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return int
     */
    public function getPostalCode(): int
    {
        return $this->postal_code;
    }

    /**
     * @param int $postal_code
     */
    public function setPostalCode(int $postal_code)
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @return string
     */
    public function getJob(): string
    {
        return $this->job;
    }

    /**
     * @param string $job
     */
    public function setJob(string $job)
    {
        $this->job = $job;
    }

    /**
     * @return string
     */
    public function getHighestGraduation(): string
    {
        return $this->highest_graduation;
    }

    /**
     * @param string $highest_graduation
     */
    public function setHighestGraduation(string $highest_graduation)
    {
        $this->highest_graduation = $highest_graduation;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge(int $age)
    {
        $this->age = $age;
    }

    /**
     * @return string
     */
    public function getRatablePath(): string
    {
        return $this->ratable_path;
    }

    /**
     * @param string $ratable_path
     */
    public function setRatablePath(string $ratable_path)
    {
        $this->ratable_path = $ratable_path;
    }

    /**
     * @return string
     */
    public function getRatingAspectName(): string
    {
        return $this->rating_aspect_name;
    }

    /**
     * @param string $rating_aspect_name
     */
    public function setRatingAspectName(string $rating_aspect_name)
    {
        $this->rating_aspect_name = $rating_aspect_name;
    }
    //endregion
}
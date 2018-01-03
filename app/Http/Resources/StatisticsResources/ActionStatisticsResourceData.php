<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 03.01.18
 * Time: 08:20
 */

namespace App\Http\Resources\StatisticsResources;


use App\IRestResourceModel;
use App\User;
use Carbon\Carbon;

class ActionStatisticsResourceData
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
    protected $education;
    /** @var int */
    protected $age;
    /** @var string */
    protected $resourcePath;

    /**
     * ActionStatisticsResourceData constructor.
     * @param string $date
     * @param User $user
     * @param string $resourcePath
     */
    public function __construct(string $date, User $user, string $resourcePath)
    {
        $this->date = $date;
        $this->gender = $user->getSex();
        $this->postal_code = $user->postal_code;
        $this->job = $user->job;
        $this->education = $user->graduation;
        $this->age = $user->getAge();
        $this->resourcePath = $resourcePath;
    }

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
    public function getEducation(): string
    {
        return $this->education;
    }

    /**
     * @param string $education
     */
    public function setEducation(string $education)
    {
        $this->education = $education;
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
    public function getResourcePath(): string
    {
        return $this->resourcePath;
    }

    /**
     * @param string $resourcePath
     */
    public function setResourcePath(string $resourcePath)
    {
        $this->resourcePath = $resourcePath;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 03.01.18
 * Time: 08:20
 */

namespace App\Http\Resources\StatisticsResources;


use App\IRestResource;
use App\User;
use Carbon\Carbon;

class GeneralActivityStatisticsResourceData
{
    /** @var string */
    protected $type;
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
    /** @var string */
    protected $extraInformation;

    /**
     * GeneralActivityStatisticsResourceData constructor.
     * @param string $type
     * @param string $date
     * @param string $gender
     * @param int $postal_code
     * @param string $job
     * @param string $education
     * @param int $age
     * @param string $resourcePath
     * @param $extraInformation
     */
    public function __construct($type, $date, $gender, $postal_code, $job, $education, $age, $resourcePath, $extraInformation)
    {
        $this->type = $type;
        $this->date = $date;
        $this->gender = $gender;
        $this->postal_code = $postal_code;
        $this->job = $job;
        $this->education = $education;
        $this->age = $age;
        $this->resourcePath = $resourcePath;
        $this->extraInformation = $extraInformation;
    }

    //region Getters and Setters
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
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

    /**
     * @return string
     */
    public function getExtraInformation(): string
    {
        return $this->extraInformation;
    }

    /**
     * @param string $extraInformation
     */
    public function setExtraInformation(string $extraInformation)
    {
        $this->extraInformation = $extraInformation;
    }
    //endregion

    public function toArray()
    {
        return [
            $this->type,
            $this->date,
            $this->gender,
            $this->postal_code,
            $this->job,
            $this->education,
            $this->age,
            $this->resourcePath,
            $this->extraInformation
        ];
    }
}
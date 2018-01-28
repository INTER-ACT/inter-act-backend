<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 09.01.18
 * Time: 08:08
 */

namespace App\Http\Resources\StatisticsResources;


use App\Http\Resources\RestResourceTrait;
use App\User;

class MultiAspectRatingStatisticsResourceData
{
    use RestResourceTrait;

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
    protected $ratable_id;
    /** @var string */
    protected $ratable_type;
    /** @var string */
    protected $ratable_path;
    /** @var string */
    protected $rated_aspects;

    /**
     * MultiAspectRatingStatisticsResourceData constructor.
     * @param string $date
     * @param User $user
     * @param int $ratable_id
     * @param string $ratable_type
     * @param string $ratable_path
     * @param array $rated_aspects
     */
    public function __construct(string $date, User $user, int $ratable_id, string $ratable_type, string $ratable_path, array $rated_aspects)
    {
        $this->date = $date;
        $this->gender = $user->getSex();
        $this->postal_code = $user->postal_code;
        $this->job = $user->job;
        $this->highest_graduation = $user->graduation;
        $this->age = $user->getAge();
        $this->ratable_id = $ratable_id;
        $this->ratable_type = $ratable_type;
        $this->ratable_path = $ratable_path;
        $this->rated_aspects = $rated_aspects;
    }

    public function toArray()
    {
        return[
            $this->date,
            $this->gender,
            $this->postal_code,
            $this->job,
            $this->highest_graduation,
            $this->age,
            $this->ratable_id,
            $this->ratable_type,
            $this->getUrl($this->ratable_path),
            implode(',', $this->rated_aspects)
        ];
    }
}
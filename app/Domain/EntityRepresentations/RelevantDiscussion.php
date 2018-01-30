<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 29.01.18
 * Time: 20:08
 */

namespace App\Domain\EntityRepresentations;

use Carbon\Carbon;

class RelevantDiscussion
{
    /** @var int */
    public $id;
    /** @var string */
    public $href;
    /** @var string */
    public $title;
    /** @var Carbon */
    public $user_interaction_date;

    /**
     * RelevantDiscussion constructor.
     * @param int $id
     * @param string $href
     * @param string $title
     * @param Carbon $user_interaction_date
     */
    public function __construct(int $id, string $href, string $title, Carbon $user_interaction_date)
    {
        $this->id = $id;
        $this->href = $href;
        $this->title = $title;
        $this->user_interaction_date = $user_interaction_date;
    }

    /**
     * @return int
     */
    public function getKey() : int
    {
        return $this->id;
    }

    public function getResourcePath(): string
    {
        return $this->href;
    }
}
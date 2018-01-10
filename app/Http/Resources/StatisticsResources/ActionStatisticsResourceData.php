<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 08.01.18
 * Time: 12:01
 */

namespace App\Http\Resources\StatisticsResources;


use App\IModel;
use Carbon\Carbon;

class ActionStatisticsResourceData
{
    /** @var string */
    protected $entity_path;
    /** @var string */
    protected $teaser;
    /** @var array */
    protected $actions;

    public function __construct(string $entity_path, string $teaser, array $actions = null)
    {
        $this->entity_path = $entity_path;
        $this->teaser = $teaser;
        $this->actions = ($actions === null) ? [] : $actions;
    }

    //region Getters and Setters
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
     * @return mixed
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param mixed $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }
    //endregion

    public function toArray()
    {
        return array_merge([
            $this->entity_path,
            $this->teaser
        ], $this->actions);
    }
}
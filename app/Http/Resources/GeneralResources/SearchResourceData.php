<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 29.12.17
 * Time: 09:43
 */

namespace App\Http\Resources\GeneralResources;


class SearchResourceData
{
    protected $discussions;
    protected $amendments;
    protected $sub_amendments;
    protected $comments;

    /**
     * SearchResourceData constructor.
     * @param $discussions
     * @param $amendments
     * @param $sub_amendments
     * @param $comments
     */
    public function __construct($discussions, $amendments, $sub_amendments, $comments)
    {
        $this->discussions = $discussions;
        $this->amendments = $amendments;
        $this->sub_amendments = $sub_amendments;
        $this->comments = $comments;
    }

    //region getters and setters
    /**
     * @return mixed
     */
    public function getDiscussions()
    {
        return $this->discussions;
    }

    /**
     * @param mixed $discussions
     */
    public function setDiscussions($discussions)
    {
        $this->discussions = $discussions;
    }

    /**
     * @return mixed
     */
    public function getAmendments()
    {
        return $this->amendments;
    }

    /**
     * @param mixed $amendments
     */
    public function setAmendments($amendments)
    {
        $this->amendments = $amendments;
    }

    /**
     * @return mixed
     */
    public function getSubAmendments()
    {
        return $this->sub_amendments;
    }

    /**
     * @param mixed $sub_amendments
     */
    public function setSubAmendments($sub_amendments)
    {
        $this->sub_amendments = $sub_amendments;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
    //endregion
}
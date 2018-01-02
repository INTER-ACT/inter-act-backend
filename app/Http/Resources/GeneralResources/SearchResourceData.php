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
    /** @var  array */
    protected $discussions;
    /** @var  array */
    protected $amendments;
    /** @var  array */
    protected $sub_amendments;
    /** @var  array */
    protected $comments;

    /**
     * SearchResourceData constructor.
     * @param array $discussions
     * @param array $amendments
     * @param array $sub_amendments
     * @param array $comments
     */
    public function __construct(array $discussions, array $amendments, array $sub_amendments, array $comments)
    {
        $this->discussions = $discussions;
        $this->amendments = $amendments;
        $this->sub_amendments = $sub_amendments;
        $this->comments = $comments;
    }

    //region getters and setters
    /**
     * @return array
     */
    public function getDiscussions(): array
    {
        return $this->discussions;
    }

    /**
     * @param array $discussions
     */
    public function setDiscussions(array $discussions)
    {
        $this->discussions = $discussions;
    }

    /**
     * @return array
     */
    public function getAmendments(): array
    {
        return $this->amendments;
    }

    /**
     * @param array $amendments
     */
    public function setAmendments(array $amendments)
    {
        $this->amendments = $amendments;
    }

    /**
     * @return array
     */
    public function getSubAmendments(): array
    {
        return $this->sub_amendments;
    }

    /**
     * @param array $sub_amendments
     */
    public function setSubAmendments(array $sub_amendments)
    {
        $this->sub_amendments = $sub_amendments;
    }

    /**
     * @return array
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array $comments
     */
    public function setComments(array $comments)
    {
        $this->comments = $comments;
    }
    //endregion
}
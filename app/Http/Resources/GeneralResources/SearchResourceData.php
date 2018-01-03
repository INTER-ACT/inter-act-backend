<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 29.12.17
 * Time: 09:43
 */

namespace App\Http\Resources\GeneralResources;


use Illuminate\Database\Eloquent\Collection;

class SearchResourceData
{
    /** @var  Collection */
    protected $discussions;
    /** @var  Collection */
    protected $amendments;
    /** @var  Collection */
    protected $sub_amendments;
    /** @var  Collection */
    protected $comments;

    /**
     * SearchResourceData constructor.
     * @param Collection $discussions
     * @param Collection $amendments
     * @param Collection $sub_amendments
     * @param Collection $comments
     */
    public function __construct(Collection $discussions, Collection $amendments, Collection $sub_amendments, Collection $comments)
    {
        $this->discussions = $discussions;
        $this->amendments = $amendments;
        $this->sub_amendments = $sub_amendments;
        $this->comments = $comments;
    }

    //region Getters and Setters
    /**
     * @return Collection
     */
    public function getDiscussions(): Collection
    {
        return $this->discussions;
    }

    /**
     * @param Collection $discussions
     */
    public function setDiscussions(Collection $discussions)
    {
        $this->discussions = $discussions;
    }

    /**
     * @return Collection
     */
    public function getAmendments(): Collection
    {
        return $this->amendments;
    }

    /**
     * @param Collection $amendments
     */
    public function setAmendments(Collection $amendments)
    {
        $this->amendments = $amendments;
    }

    /**
     * @return Collection
     */
    public function getSubAmendments(): Collection
    {
        return $this->sub_amendments;
    }

    /**
     * @param Collection $sub_amendments
     */
    public function setSubAmendments(Collection $sub_amendments)
    {
        $this->sub_amendments = $sub_amendments;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Collection $comments
     */
    public function setComments(Collection $comments)
    {
        $this->comments = $comments;
    }
    //endregion
}
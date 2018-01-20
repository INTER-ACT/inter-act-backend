<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:23
 */

namespace App\Domain;


use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\PostResources\TagResource;
use App\Tags\Tag;

class TagRepository implements IRestRepository   //TODO: Exceptions missing?
{
    use CustomPaginationTrait;

    /**
     * @return string
     */
    public function getRestResourcePath()
    {
        return "/tags";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return "tags";
    }

    /**
     * @return string
     */
    public function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }

    /**
     * @return TagCollection
     */
    public function getAll() : TagCollection
    {
        return new TagCollection(Tag::all());
    }

    /**
     * @param int $id
     * @return TagResource
     */
    public function getById(int $id) : TagResource
    {
        return new TagResource(Tag::find($id));
    }
}
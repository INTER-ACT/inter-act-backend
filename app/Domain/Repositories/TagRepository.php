<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:23
 */

namespace App\Domain;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\PostResources\TagResource;
use App\Tags\Tag;

class TagRepository implements IRestRepository
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

    /**
     * @param int $id
     * @return Tag
     * @throws InvalidValueException
     * @throws ResourceNotFoundException
     */
    public static function getByIdOrThrowError(int $id) : Tag
    {
        if(!isset($id) or !is_numeric($id))
            throw new InvalidValueException("The given id was invalid.");
        $id = (int)$id;
        /** @var Tag $tag */
        $tag = Tag::find($id);
        if($tag === null)
            throw new ResourceNotFoundException('Tag with id ' . $id . ' not found.');
        return $tag;
    }

    /**
     * @param string $name
     * @return Tag
     * @throws ResourceNotFoundException
     */
    public static function getByName(string $name) : Tag
    {
        /** @var Tag $tag */
        $tag = Tag::where('name', '=', $name)->first();
        if($tag === null)
            throw new ResourceNotFoundException('Tag with name ' . $name . ' not found.');
        return $tag;
    }
}
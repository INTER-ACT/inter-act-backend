<?php

namespace App\Http\Controllers;

use App\Domain\TagRepository;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\PostResources\TagResource;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /** @var TagRepository */
    protected $repository;

    /**
     * TagController constructor.
     * @param TagRepository $repository
     */
    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return TagCollection
     */
    public function index() : TagCollection
    {
        return $this->repository->getAll();
    }

    /**
     * @param int $id
     * @return TagResource
     */
    public function show(int $id) : TagResource
    {
        return $this->repository->getById($id);
    }
}

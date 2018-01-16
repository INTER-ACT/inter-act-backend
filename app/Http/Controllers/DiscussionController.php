<?php

namespace App\Http\Controllers;

use App\Domain\DiscussionRepository;
use App\Domain\Manipulators\DiscussionManipulator;
use App\Domain\OgdRisApiBridge;
use App\Domain\PageRequest;
use App\Http\Resources\AmendmentResources\AmendmentCollection;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
use App\Http\Resources\DiscussionResources\DiscussionResource;
use App\Http\Resources\DiscussionResources\DiscussionStatisticsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DiscussionController extends Controller
{
    /** @var DiscussionRepository */
    protected $repository;

    /**
     * DiscussionController constructor.
     * @param DiscussionRepository $repository
     */
    public function __construct(DiscussionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return DiscussionCollection
     */
    public function index() : DiscussionCollection
    {
        $perPage = Input::get('count', 100);
        $pageNumber = Input::get('start', 1);
        $tag_id = Input::get('tag_id', null);
        $sorted_by = Input::get('sorted_by', '');
        $sort_dir = Input::get('sort_direction', '');
        return $this->repository->getAll(new PageRequest($perPage, $pageNumber), $sorted_by, $sort_dir, $tag_id);
    }

    /**
     * @param int $id
     * @return DiscussionResource
     */
    public function show(int $id) : DiscussionResource
    {
        return $this->repository->getById($id);
    }

    /**
     * @param int $id
     */
    public function update(int $id) : void
    {
        DiscussionManipulator::update($id, Input::all());
    }

    /**
     * @param int $id
     */
    public function destroy(int $id)
    {
        DiscussionManipulator::delete($id);
    }

    /**
     * @param int $id
     * @return AmendmentCollection
     */
    public function listAmendments(int $id) : AmendmentCollection
    {
        $perPage = Input::get('count', 10);
        $pageNumber = Input::get('start', 1);
        $sorted_by = Input::get('sorted_by', '');
        $sort_dir = Input::get('sort_direction', '');
        return $this->repository->getAmendments($id, $sorted_by, $sort_dir, new PageRequest($perPage, $pageNumber));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return int
     */
    public function createAmendment(int $id, Request $request) : int    //TODO: change to CreateAmendmentRequest
    {
        return DiscussionManipulator::createAmendment($id, $request->all());
    }

    /**
     * @param int $id
     * @return CommentCollection
     */
    public function listComments(int $id) : CommentCollection
    {
        $perPage = Input::get('count', 10);
        $pageNumber = Input::get('start', 1);
        return $this->repository->getComments($id, new PageRequest($perPage, $pageNumber));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return int
     */
    public function createComment(int $id, Request $request) : int    //TODO: change to CreateCommentRequest
    {
        return DiscussionManipulator::createComment($id, $request->all());
    }

    /**
     * @param int $id
     * @return DiscussionStatisticsResource
     */
    public function showStatistics(int $id) : DiscussionStatisticsResource  //TODO: remove if not needed
    {
        return $this->repository->getStatistics($id);
    }

    /**
     * @return string
     */
    public function listLawTexts()
    {
        return OgdRisApiBridge::getAllTexts();
    }
}

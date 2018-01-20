<?php

namespace App\Http\Controllers;

use App\CommentRating;
use App\Domain\CommentRepository;
use App\Domain\Manipulators\CommentManipulator;
use App\Domain\PageRequest;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CommentResources\CommentResource;
use App\Http\Resources\PostResources\ReportCollection;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class CommentController extends Controller
{
    /** @var CommentRepository */
    protected $repository;

    public function __construct(CommentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return CommentCollection
     */
    public function index() : CommentCollection
    {
        $perPage = Input::get('count', 20);
        $pageNumber = Input::get('start', 1);
        //$tag_id = Input::get('tag_id', null);
        //$sorted_by = Input::get('sorted_by', '');
        //$sort_dir = Input::get('sort_direction', '');
        return $this->repository->getAll(new PageRequest($perPage, $pageNumber));
    }

    /**
     * @param Request $request
     * @return int
     */
    public function store(Request $request) : int   //TODO: implement or remove CommentController->store()
    {
        //CommentManipulator::create($request);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return CommentResource
     */
    public function show(Request $request, int $id)// : CommentResource
    {
        if($request->has('fields'))
            return $this->repository->getById($id, explode(',', $request->query('fields')));
        return $this->repository->getById($id);
    }

    /**
     * @param int $id
     * @return void
     */
    public function destroy(int $id)
    {
        CommentManipulator::delete($id);
    }

    /**
     * @param int $id
     * @return CommentCollection
     */
    public function listComments(int $id) : CommentCollection
    {
        return $this->repository->getComments($id);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return int
     */
    public function createComment(Request $request, int $id) : int  //TODO: update to CreateCommentRequest (???)
    {
        return CommentManipulator::createComment($id, $request->all());
    }

    public function showRating(Request $request, int $id)
    {
        return $this->repository->getRating($id, 1)->toArray($request, User::find(1));    //TODO: change '1' to Auth::id()
    }

    //TODO: change UpdateMARatingRequest to UpdateCommentRatingRequest in docs
    public function updateRating(int $id, Request $request) //TODO: change Request to UpdateCommentRatingRequest
    {
        CommentManipulator::updateRating($id, $request->all()['rating_score'], Auth::id());
    }

    public function listReports(int $id) : ReportCollection
    {
        return $this->repository->getReports($id);
    }

    public function createReport(int $id, Request $request) //TODO: change Request to CreateReportRequest
    {
        CommentManipulator::createReport($id, $request->all(), Auth::id());
    }
}

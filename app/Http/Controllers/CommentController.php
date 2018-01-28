<?php

namespace App\Http\Controllers;

use App\CommentRating;
use App\Domain\CommentRepository;
use App\Domain\Manipulators\CommentManipulator;
use App\Domain\PageGetRequest;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\CreateReportRequest;
use App\Http\Requests\DeleteCommentRequest;
use App\Http\Requests\TagRecommendationsRequest;
use App\Http\Requests\UpdateCommentRatingRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResources\CommentCollection;
use App\Http\Resources\CommentResources\CommentResource;
use App\Http\Resources\NoContentResource;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\PostResources\TagCollection;
use App\Http\Resources\SuccessfulCreationResource;
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
     * @param Request $request
     * @return CommentCollection
     */
    public function index(Request $request) : CommentCollection
    {
        return $this->repository->getAll(new PageGetRequest($request));
    }

    /**
     * @param Request $request
     * @return SuccessfulCreationResource
     * @throws \Exception
     */
    public function store(Request $request) : SuccessfulCreationResource   //TODO: implement or remove CommentController->store()
    {
        throw new \Exception("function store not implemented in CommentController.");
        //CommentManipulator::create($request);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return CommentResource
     * @throws InvalidValueException
     */
    public function show(Request $request, $id) : CommentResource
    {
        if(!is_numeric($id))
            throw new InvalidValueException("The given id was not valid");
        return $this->repository->getById($id);
    }

    /**
     * @param UpdateCommentRequest $request
     * @param $id
     * @return NoContentResource
     * @throws InvalidValueException
     */
    public function update(UpdateCommentRequest $request, $id) : NoContentResource
    {
        if(!is_numeric($id))
            throw new InvalidValueException("The given id was not valid");
        return CommentManipulator::update($id, $request->all()['tags']);
    }

    /**
     * @param DeleteCommentRequest $request
     * @param int $id
     * @return NoContentResource
     */
    public function destroy(DeleteCommentRequest $request, int $id) : NoContentResource
    {
        CommentManipulator::delete($id);
        return new NoContentResource();
    }

    /**
     * @param Request $request
     * @param int $id
     * @return CommentCollection
     */
    public function listComments(Request $request, int $id) : CommentCollection
    {
        return $this->repository->getComments($id, new PageGetRequest($request));
    }

    /**
     * @param CreateCommentRequest $request
     * @param int $id
     * @return SuccessfulCreationResource
     */
    public function createComment(CreateCommentRequest $request, int $id) : SuccessfulCreationResource
    {
        return CommentManipulator::createComment($id, $request->all());
    }

    /*public function showRating(Request $request, int $id)
    {
        return $this->repository->getRating($id, 1)->toArray($request, User::find(1));
    }*/

    //TODO: change UpdateMARatingRequest to UpdateCommentRatingRequest in docs
    /**
     * @param UpdateCommentRatingRequest $request
     * @param int $id
     * @return NoContentResource
     */
    public function updateRating(UpdateCommentRatingRequest $request, int $id) : NoContentResource
    {
        $rating = $request->input('user_rating');
        if($rating == 0)
            return CommentManipulator::destroyRating($id, Auth::id());
        return CommentManipulator::updateRating($id, $rating, Auth::id());
    }

    /**
     * @param int $id
     * @return ReportCollection
     */
    public function listReports(int $id) : ReportCollection
    {
        return $this->repository->getReports($id);
    }

    public function createReport(int $id, CreateReportRequest $request)
    {
        CommentManipulator::createReport($id, $request->all(), Auth::id()); //TODO: implement
    }

    /**
     * @param TagRecommendationsRequest $request
     * @return TagCollection
     */
    public function getTagsForText(TagRecommendationsRequest $request) : TagCollection
    {
        $text = $request->input('text');
        return $this->repository->getTagRecommendations($text);
    }
}

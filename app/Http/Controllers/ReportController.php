<?php

namespace App\Http\Controllers;

use App\Domain\Manipulators\ReportManipulator;
use App\Domain\PageGetRequest;
use App\Domain\ReportRepository;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Http\Requests\CreateReportRequest;
use App\Http\Requests\CreateReportRequest2;
use App\Http\Requests\ListReportsRequest;
use App\Http\Requests\ViewReportRequest;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\SuccessfulCreationResource;

class ReportController extends Controller
{
    /** @var ReportRepository */
    protected $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ListReportsRequest $request
     * @return ReportCollection
     */
    public function index(ListReportsRequest $request) : ReportCollection
    {
        $type = $request->type;
        $user_id = $request->user_id;
        if(empty($user_id) or !is_numeric($user_id))
            $user_id = 0;
        if(empty($type))
            return $this->repository->getAll(new PageGetRequest($request), $user_id);
        else
            return $this->repository->getAllOfType(new PageGetRequest($request), $type, $user_id);
    }

    public function show(ViewReportRequest $request, $id)
    {
        if(empty($id) or !is_numeric($id))
            throw new InvalidValueException('The given id was not an integer.');
        return $this->repository->getById($id);
    }

    /**
     * @param CreateReportRequest2 $request
     * @return SuccessfulCreationResource
     */
    public function create(CreateReportRequest2 $request) : SuccessfulCreationResource
    {
        return ReportManipulator::create(\Auth::user(), $request->all());
    }
}

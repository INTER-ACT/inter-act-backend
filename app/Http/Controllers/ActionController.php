<?php

namespace App\Http\Controllers;

use App\Domain\ActionRepository;
use App\Domain\PageGetRequest;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\ViewStatisticsRequest;
use App\Http\Resources\GeneralResources\SearchResource;
use App\Http\Resources\GraduationListResource;
use App\Http\Resources\JobListResource;
use App\Http\Resources\StatisticsResources\StatisticsResource;
use App\Http\Resources\StatisticsResources\UserActivityStatisticsResource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActionController extends Controller
{
    /** @var ActionRepository */
    protected $repository;

    /**
     * ActionController constructor.
     * @param ActionRepository $repository
     */
    public function __construct(ActionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param SearchRequest $request
     * @return SearchResource
     * @throws InvalidValueException
     */
    public function searchArticles(SearchRequest $request) : SearchResource
    {
        $search_term = $request->search_term;
        $type = $request->type;
        $content_types = $request->has('content_type') ? explode(',', $request->content_type) : null;

        return $this->repository->searchArticlesByText($search_term, new PageGetRequest($request), $type, $content_types);
    }

    /**
     * @param ViewStatisticsRequest $request
     * @return StreamedResponse
     */
    public function getGeneralActivityStatistics(ViewStatisticsRequest $request) : StreamedResponse
    {
        $data = $this->repository->getGeneralActivityStatistics($request->begin, $request->end)->toArray();
        return new StreamedResponse(
            function() use($data)
            {
                // A resource pointer to the output stream for writing the CSV to
                $handle = fopen('php://output', 'w');
                foreach ($data as $row)
                {
                    // Loop through the data and write each entry as a new row in the csv
                    fputcsv($handle, $row);
                }
                fclose($handle);
            },
            200,
            [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=GeneralActivityStatistics.csv'
            ]
        );
    }

    /**
     * @param ViewStatisticsRequest $request
     * @return StreamedResponse
     */
    public function getRatingStatistics(ViewStatisticsRequest $request) : StreamedResponse
    {
        $data = $this->repository->getRatingStatisticsResource($request->begin, $request->end)->toArray();
        return new StreamedResponse(
            function() use($data)
            {
                $handle = fopen('php://output', 'w');
                foreach ($data as $row)
                {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            200,
            [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=MultiAspectRatingStatistics.csv'
            ]
        );
    }

    /**
     * @param Request $request
     * @return StreamedResponse
     */
    public function getCommentRatingStatistics(Request $request) : StreamedResponse
    {
        $data = $this->repository->getCommentRatingStatisticsResource($request->begin, $request->end)->toArray();
        return new StreamedResponse(
            function() use($data)
            {
                $handle = fopen('php://output', 'w');
                foreach ($data as $row)
                {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            200,
            [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=CommentRatingStatistics.csv'
            ]
        );
    }

    public function getUserActivityStatistics(Request $request) : StreamedResponse
    {
        $data = $this->repository->getUserActivityStatisticsResource($request->user_id)->toArray();
        return new StreamedResponse(
            function() use($data)
            {
                // A resource pointer to the output stream for writing the CSV to
                $handle = fopen('php://output', 'w');
                foreach ($data as $row)
                {
                    // Loop through the data and write each entry as a new row in the csv
                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            200,
            [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=RatingStatistics.csv'
            ]
        );
    }

    public function getObjectActivityStatistics(Request $request) : StreamedResponse
    {
        $data = $this->repository->getObjectActivityStatisticsResource()->toArray();
        return new StreamedResponse(
            function() use($data)
            {
                $handle = fopen('php://output', 'w');
                foreach ($data as $row)
                {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            200,
            [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=ObjectActivityStatistics.csv'
            ]
        );
    }

    /**
     * @return JobListResource
     */
    public function getJobList() : JobListResource
    {
        return $this->repository->getJobList();
    }

    /**
     * @return GraduationListResource
     */
    public function getGraduationList() : GraduationListResource
    {
        return $this->repository->getGraduationList();
    }
}

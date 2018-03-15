<?php

namespace App\Http\Controllers;

use App\Domain\ActionRepository;
use App\Domain\PageGetRequest;
use App\Domain\PageRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\ViewStatisticsRequest;
use App\Http\Requests\ViewUserDetailsRequest;
use App\Http\Resources\AspectListResource;
use App\Http\Resources\DiscussionResources\DiscussionCollection;
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
     * @param ViewStatisticsRequest $request
     * @return StreamedResponse
     */
    public function getCommentRatingStatistics(ViewStatisticsRequest $request) : StreamedResponse
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

    /**
     * @param ViewStatisticsRequest $request
     * @return StreamedResponse
     */
    public function getUserActivityStatistics(ViewStatisticsRequest $request) : StreamedResponse
    {
        $user_id = $request->user_id;
        if(!isset($user_id) or !is_numeric($user_id))
            $user_id = 0;
        $data = $this->repository->getUserActivityStatisticsResource($user_id)->toArray();
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

    /**
     * @param ViewStatisticsRequest $request
     * @return StreamedResponse
     */
    public function getObjectActivityStatistics(ViewStatisticsRequest $request) : StreamedResponse
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
     * @param ViewUserDetailsRequest $request
     * @param $user_id
     * @return DiscussionCollection
     * @throws InvalidValueException
     */
    public function getRelevantDiscussions(ViewUserDetailsRequest $request, $user_id) : DiscussionCollection
    {
        if(!is_numeric($user_id))
            throw new InvalidValueException('The user_id has to be an integer.');
        return $this->repository->getRelevantDiscussions($user_id);
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

    /**
     * @return AspectListResource
     */
    public function getAspects() : AspectListResource
    {
        return $this->repository->getAspects();
    }
}

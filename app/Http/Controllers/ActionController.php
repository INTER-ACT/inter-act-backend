<?php

namespace App\Http\Controllers;

use App\Domain\ActionRepository;
use App\Domain\PageGetRequest;
use App\Domain\PageRequest;
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

    public function searchArticles(Request $request)
    {
        $search_term = $request->search_term;
        $type = $request->type;
        $post_type = $request->post_type;
        return $this->repository->searchArticlesByText($search_term, new PageGetRequest($request), $type, $post_type);
    }

    /**
     * @param Request $request
     * @return StreamedResponse
     */
    public function getGeneralStatistics(Request $request) : StreamedResponse
    {
        $data = $this->repository->getGeneralActivityStatistics($request->start_date, $request->end_date)->toArray();
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

    public function getRatingStatistics(Request $request) : StreamedResponse
    {
        $data = $this->repository->getRatingStatisticsResource()->toArray();
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

    public function getCommentRatingStatistics(Request $request) : StreamedResponse
    {
        $data = $this->repository->getCommentRatingStatisticsResource()->toArray();
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
                'Content-Disposition' => 'attachment; filename=CommentRatingStatistics.csv'
            ]
        );
    }
}

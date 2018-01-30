<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:56
 */

namespace App\Domain;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\PostResources\ReportCollection;
use App\Http\Resources\PostResources\ReportResource;
use App\Reports\Report;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class ReportRepository implements IRestRepository
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

    public function getAll(PageRequest $pageRequest, int $user_id = 0)
    {
        if($user_id == 0) {
            $reports = Report::select('id')->paginate($pageRequest->perPage, ['*'], 'start', $pageRequest->pageNumber);
        }
        else {
            /** @var Collection $reports_raw */
            $reports_raw = Report::select('id', 'reportable_id', 'reportable_type')->with('reportable:id,user_id')->get();
            $reports_raw = $reports_raw->filter(function(Report $item) use($user_id){
                return $item->reportable->user_id == $user_id;
            });
            $reports = $this->paginate($reports_raw, $pageRequest->perPage, $pageRequest->pageNumber);
        }
        $this->updatePagination($reports);
        return new ReportCollection($reports);
    }

    public function getAllOfType(PageRequest $pageRequest, string $type, int $user_id = 0)
    {
        $types = ['amendments' => Amendment::class, 'subamendments' => SubAmendment::class, 'comments' => Comment::class];
        if(!array_key_exists($type, $types))
            throw new InvalidValueException('The given type was not valid.');
        $type = $types[$type];
        if($user_id == 0) {
            $reports = Report::select(['id', 'reportable_id', 'reportable_type'])->where('reportable_type', '=', $type)->paginate($pageRequest->perPage, ['*'], 'start', $pageRequest->pageNumber);
        }
        else {
            /** @var Collection $reports_raw */
            $reports_raw = Report::select(['id', 'reportable_id', 'reportable_type'])->where('reportable_type', '=', $type)->with('reportable:id,user_id')->get();
            $reports_raw = $reports_raw->filter(function(Report $item) use($user_id){
                return $item->reportable->user_id == $user_id;
            });
            $reports = $this->paginate($reports_raw, $pageRequest->perPage, $pageRequest->pageNumber);
        }
        $this->updatePagination($reports);
        return new ReportCollection($reports);
    }

    public function getById(int $id) : ReportResource
    {
        return new ReportResource(self::getByIdOrThrowError($id));
    }

    public static function getByIdOrThrowError(int $id)
    {
        /** @var Report $discussion */
        $report = Report::find($id);
        if($report === null)
            throw new ResourceNotFoundException('Report with id ' . $id . ' not found.');
        return $report;
    }
}
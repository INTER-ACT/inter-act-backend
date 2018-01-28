<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 24.01.18
 * Time: 18:25
 */

namespace App\Domain;


use App\Exceptions\CustomExceptions\InvalidPaginationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PageGetRequest extends PageRequest
{
    use CustomPaginationTrait;

    public $request;

    /**
     * PageGetRequest constructor.
     * @param Request $request
     * @throws InvalidPaginationException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $count = $request->input('count', null);
        $start = $request->input('start', null);
        if(!isset($count))
            $count = self::DEFAULT_PER_PAGE;
        else if(!is_numeric($count))
            throw new InvalidPaginationException("The given count for the pagination has to be an integer.");
        if(!isset($start))
            $start = self::DEFAULT_PAGE_NUMBER;
        else if(!is_numeric($start))
            throw new InvalidPaginationException("The given start for the pagination has to be an integer.");
        parent::__construct($count, $start);
    }

    /**
     * @param Collection $collection
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedCollection(Collection $collection)
    {
        return $this->paginate($collection, $this->perPage, $this->pageNumber);
    }
}
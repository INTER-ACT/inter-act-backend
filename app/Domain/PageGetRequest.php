<?php

namespace App\Domain;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Input;

class PageGetRequest extends PageRequest
{
    use CustomPaginationTrait;

    /** @var string */
    public $sortedBy;
    /** @var string */
    public $sortDirection;

    /**
     * PageRequest constructor.
     *
     * Fetch Get Params and assign them default values, if they do not exist
     */
    public function __construct()
    {
        parent::__construct(Input::get('count', null), Input::get('start', null));

        $this->sortedBy = Input::get('sorted_by', Null);
        $this->sortDirection = Input::get('sort_direction', 'desc');
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
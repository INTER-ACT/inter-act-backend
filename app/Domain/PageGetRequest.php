<?php

namespace App\Domain;


use Illuminate\Support\Facades\Input;

class PageGetRequest
{
    public $perPage;
    public $pageNumber;

    public $sortedBy;
    public $sortDirection;

    /**
     * PageRequest constructor.
     *
     * Fetch Get Params and assign them default values, if they do not exist
     */
    public function __construct()
    {
        $this->perPage = Input::get('count', 20);
        $this->pageNumber = Input::get('start', 1);

        $this->sortedBy = Input::get('sorted_by', Null);
        $this->sortDirection = Input::get('sort_direction', 'desc');
    }

    public function getPaginatedCollection($collection)
    {
        return $collection->paginate($collection, $this->perPage, $this->pageNumber);
    }
}
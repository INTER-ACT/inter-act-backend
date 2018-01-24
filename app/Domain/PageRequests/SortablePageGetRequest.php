<?php

namespace App\Domain;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class SortablePageGetRequest extends PageGetRequest
{
    /** @var string */
    public $sortedBy;
    /** @var string */
    public $sortDirection;

    /**
     * SortablePageGetRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->sortedBy = $request->input('sorted_by', Null);
        $this->sortDirection = $request->input('sort_direction', 'desc');
    }
}
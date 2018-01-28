<?php

namespace App\Http\Requests;


use App\Domain\AmendmentRepository;
use App\Domain\PageGetRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use Illuminate\Http\Request;

/**
 * Request for getting a list of amendments, handling sorting
 *
 * @package App\Http\Requests
 */
class GetAmendmentsRequest extends PageGetRequest
{
    public $sortedBy;
    public $sortDirection;

    /**
     * GetAmendmentsRequest constructor.
     *
     * @param Request $request
     * @throws InvalidValueException
     */
    public function __construct(Request $request)
    {
        $this->sortDirection = $request->input('sort_direction', AmendmentRepository::SORT_DESC);
        if(!($this->sortDirection == AmendmentRepository::SORT_DESC || $this->sortDirection == AmendmentRepository::SORT_ASC))
            throw new InvalidValueException("$this->sortDirection is not a valid sorting direction");

        $this->sortedBy = $request->input('sorted_by', AmendmentRepository::SORT_BY_POPULARITY);
        if(!($this->sortedBy == AmendmentRepository::SORT_BY_POPULARITY || $this->sortedBy == AmendmentRepository::SORT_BY_CHRONOLOGICAL))
            throw new InvalidValueException("$this->sortedBy is not a valid sorting criteria");

        parent::__construct($request);
    }
}
<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\Domain\ReportRepository;

class ViewReportRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $report = ReportRepository::getByIdOrThrowError($this->route('report_id'));
        return $this->user()->can('view', $report);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}

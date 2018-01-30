<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\Permission;
use App\Role;

class ViewStatisticsRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasPermission(Permission::getAnalyze());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'begin' => 'date_format:Y-m-d',
            'end' => 'date_format:Y-m-d'
        ];
    }
}

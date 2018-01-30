<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
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
        return true;//return $this->user()->hasRole(Role::getScientist()) || $this->user()->hasRole(Role::getAdmin());
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

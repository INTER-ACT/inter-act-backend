<?php

namespace App\Http\Requests;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Domain\ApiRequest;
use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Input;

class CreateReportRequest2 extends ApiRequest
{
    const VALID_TYPE_VALUES = ['comment', 'amendment', 'subamendment'];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reportable_type' => 'required|string',
            'reportable_id' => 'required|integer|poly_exists:reportable_type',
            'explanation' => 'required|string'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if($validator->errors()->has('reportable_id'))
            throw new CannotResolveDependenciesException($validator->errors()->first('reported_id'));
        parent::failedValidation($validator);
    }

    public function validate()
    {
        $current = $this->all();
        $map_array = [
            'comment' => Comment::class,
            'subamendment' => SubAmendment::class,
            'amendment' => Amendment::class
        ];
        if(!array_key_exists($current['reported_type'], $map_array))
            throw new InvalidValueException('Invalid value for reported_type');
        $new_data = [
            'reportable_id' => $current['reported_id'],
            'reportable_type' => $map_array[$current['reported_type']],
            'explanation' => $current['description']
        ];
        Input::replace($new_data);
        parent::validate();
    }
}

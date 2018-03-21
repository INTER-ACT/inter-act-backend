<?php

namespace App\Http\Requests;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
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
            'reported_type' => 'required|string',
            'reportable_id' => 'required|integer',
            'description' => 'required|string'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if($validator->errors()->has('reportable_id'))
            throw new InvalidValueException($validator->errors()->first('reportable_id'));
        parent::failedValidation($validator);
    }

    public function validate()
    {
        parent::validate();
        $current = $this->all();
        $map_array = [
            'comment' => Comment::class,
            'subamendment' => SubAmendment::class,
            'amendment' => Amendment::class
        ];
        if(!array_key_exists($current['reported_type'], $map_array))
            throw new InvalidValueException('Invalid value for reported_type');
        $new_data = [
            'reportable_id' => $current['reportable_id'],
            'reportable_type' => $map_array[$current['reported_type']],
            'explanation' => $current['description']
        ];
        if(!$this->validatePolyExists($new_data['reportable_type'], $new_data['reportable_id']))
            throw new CannotResolveDependenciesException('The reported object could not be found.');
        Input::replace($new_data);
    }

    protected function validatePolyExists(string $type, int $id)
    {
        switch ($type){
            case Comment::class:
                return Comment::find($id) != null;
                break;
            case Amendment::class:
                return Amendment::find($id) != null;
                break;
            case SubAmendment::class:
                return SubAmendment::find($id) != null;
                break;
            default:
                throw new InvalidValueException('Invalid value given for the field reported_type');
        }
    }
}

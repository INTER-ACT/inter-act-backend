<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 24.01.18
 * Time: 19:57
 */

namespace App\Domain;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    /**
     * @param Validator $validator
     * @throws InvalidValueException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new InvalidValueException($validator->errors()->toJson());
    }

    /**
     * @throws NotPermittedException
     */
    protected function failedAuthorization()
    {
        throw new NotPermittedException("The user is not permitted to perform this request.");
    }
}
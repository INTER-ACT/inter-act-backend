<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 30.01.18
 * Time: 10:33
 */

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use Illuminate\Contracts\Validation\Validator;

trait RequestHasTagsTrait
{
    /**
     * @param Validator $validator
     * @throws CannotResolveDependenciesException
     */
    protected function failedValidation(Validator $validator)
    {
        if($validator->errors()->has('tags.*'))
            throw new CannotResolveDependenciesException($validator->errors()->first('tags.*'));
        parent::failedValidation($validator);
    }
}
<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use Request;
use Validator;

class UpdateUserRequest extends AUserRequest
{
    public $request;

    /**
     * UpdateUserRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Validates the Request and throws an Exception, if the request
     * is ambiguous
     *
     * @return Null
     */
    public function validate()
    {
        $this->checkFields();
        $this->checkPasswordValidity($this->request->input('password'));
    }

    /**
     * Verifies that the values have the correct properties
     *
     */
    private function checkFields()
    {
        $validator = Validator::make($this->request->all(),[
            'email' => 'sometimes|required|unique:users|max:256|email',
            'password' => 'sometimes|required|min:8|string',// TODO implement more sophisticated password updates
            'postal_code' => 'sometimes|required|size:4|numeric',     // TODO check whether place exists
            'residence' => 'sometimes|required|string',
            'job' => 'sometimes|required',
            'highest_education' => 'sometimes|required'               // TODO check whether the education exists
        ]);

        if($validator->fails()){
            throw new InvalidValueException($validator->errors());      // TODO there might should be a MissingArgumentException instead
        }
    }

    public function getData()
    {
        return $this->request->all();
    }
}
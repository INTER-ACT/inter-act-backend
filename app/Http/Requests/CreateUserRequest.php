<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Rules\Gender;
use Auth;
use Request;
use Validator;

class CreateUserRequest extends AUserRequest
{
    public $request;

    /**
     * CreateUserRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getData()
    {
        return $this->request->all();
    }

    /**
     * Validates the Request and throws an Exception, if the request
     * is ambiguous
     *
     *
     * @return Null
     */
    public function validate()
    {
        $this->checkFields();
        $this->checkPasswordValidity($this->request->input('password'));
    }


    /**
     * Verifies that all values for creating a user exist
     * and have valid values (dependencies are not tested here, as well as password strength and email syntax)
     *
     */
    private function checkFields()
    {
        $validator = Validator::make($this->request->all(),[
            'username' => 'required|unique:users|min:3|max:30|string',
            'email' => 'required|unique:users|max:256|email',
            'password' => 'required|min:8|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'sex' => ['required', 'string', 'size:1', new Gender],
            'postal_code' => 'required|size:4|numeric',     // TODO check whether place exists
            'residence' => 'required|string',
            'job' => 'required',
            'highest_education' => 'required'               // TODO check whether the education exists
        ]);

        if($validator->fails()){
            throw new InvalidValueException($validator->errors());      // TODO there might should be a MissingArgumentException instead
        }
    }
}
<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Rules\Gender;
use Auth;
use Illuminate\Http\Request;
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
            'username' => 'required|unique:users|unique:pending_users|min:3|max:64|string',
            'email' => 'required|unique:users|max:254|email',
            'password' => 'required|min:10|max:25|string',
            'first_name' => 'required|string|min:1|max:64',
            'last_name' => 'required|string|min:1|max:64',
            'sex' => ['required', 'string', 'size:1', new Gender],
            'postal_code' => 'required|min:1000|max:9999|numeric',     // TODO check whether place exists
            'residence' => 'required|string|min:1|max:254',
            'job' => 'required|string|min:1|max:254',
            'highest_education' => 'required|string|min:1|max:254',               // TODO check whether the education exists
            'year_of_birth' => 'required|numeric|min:1900|max:' . date('YYYY')
        ]);

        if($validator->fails()){
            throw new InvalidValueException($validator->errors());      // TODO there might should be a MissingArgumentException instead
        }
    }
}
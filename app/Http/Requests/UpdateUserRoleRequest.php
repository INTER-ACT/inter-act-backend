<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Role;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Validator;

class UpdateUserRoleRequest extends AUserRequest
{
    public $request;

    /**
     * UpdateUserRoleRequest constructor.
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
     * @return Null
     */
    public function validate()
    {
        $this->checkFields();
    }


    /**
     * Verifies that the role exists
     */
    private function checkFields()
    {
        $validator = Validator::make($this->request->all(),[
            'role' => [
                'required',
                Rule::in(Role::getAllRoleNames())
            ]
        ]);

        if($validator->fails()){
            throw new InvalidValueException($validator->errors());      // TODO there might should be a MissingArgumentException instead
        }
    }
}
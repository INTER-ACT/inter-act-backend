<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
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
     * @throws InvalidValueException
     */
    public function validate()
    {
        $this->checkFields();

        if($this->request->exists('password')){
            $this->checkPasswordValidity($this->request->input('password'));

            if(!Hash::check($this->request->input('old_password'), Auth::user()->password))
                throw new InvalidValueException('The old password is wrong');
        }
    }

    /**
     * Verifies that the values have the correct properties

     * @throws InvalidValueException
     */
    private function checkFields()
    {
        $validator = Validator::make($this->request->all(),[
            'email' => 'sometimes|required|unique:users|max:254|email',
            'password' => 'sometimes|required|min:8|max:25|string',// TODO implement more sophisticated password updates
            'old_password' => 'required_with:password|string|min:8|max:25',
            'postal_code' => 'sometimes|required|size:4|numeric',     // TODO check whether place exists
            'residence' => 'sometimes|required|string|min:1|max:254',
            'job' => 'sometimes|required|string|min:1|max:254',
            'highest_education' => 'sometimes|required|string|min:1|max:254'               // TODO check whether the education exists
        ]);

        if($validator->fails()){
            throw new InvalidValueException($validator->errors());      // TODO there might should be a MissingArgumentException instead
        }
    }

    public function getData()
    {
        $data = $this->request->all();
        $data['pending_password'] = $data['password'];
        $this->exchangeKey($data, 'residence', 'city');
        $this->exchangeKey($data, 'highest_education', 'graduation');

        return $data;
    }

    private function exchangeKey(array &$data, string $oldKey, string $newKey)
    {
        if(isset($data[$oldKey]))
        {
            $data[$newKey] = $data[$oldKey];
            unset($data[$oldKey]);
        }
    }
}
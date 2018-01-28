<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use Illuminate\Http\Request;
use Validator;

class CreateSubAmendmentRequest implements IRequest
{
    public $request;

    /**
     * CreateSubAmendmentRequest constructor.
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
        $validator = Validator::make($this->request->all(), [
            'updated_text' => 'required|string|min:1|max:10000',
            'explanation' => 'required|string|min:1|max:10000',
            'tags' => 'required|array',
            'tags.*' => 'required|integer|exists:tags,id|distinct'
        ]);

        if($validator->fails())
            throw new InvalidValueException($validator->errors());
    }

    public function getData()
    {
        return $this->request->all();
    }
}
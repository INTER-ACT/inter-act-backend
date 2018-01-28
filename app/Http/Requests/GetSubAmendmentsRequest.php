<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class GetSubAmendmentsRequest extends GetAmendmentsRequest
{
    public $state;

    public function __construct(Request $request)
    {
        $this->validate($request);
        $this->state = $request->input('status',  'active');

        parent::__construct($request);
    }

    private function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => [
                'sometimes',
                Rule::in(['active', 'accepted', 'rejected', 'all'])
            ]
        ]);

        if($validator->fails())
            throw new InvalidValueException($validator->errors());
    }
}
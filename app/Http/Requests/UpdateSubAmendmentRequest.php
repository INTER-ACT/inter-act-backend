<?php

namespace App\Http\Requests;


use App\Domain\AmendmentRepository;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use Auth;
use Illuminate\Http\Request;
use Validator;

class UpdateSubAmendmentRequest implements IRequest
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
     * Fails if the logged in user is not the author of the Amendment
     *
     * @param int $amendmnet_id
     * @throws NotPermittedException
     */
    public function authorize(int $amendmnet_id)
    {
        $user = Auth::user();
        $amendmnet = AmendmentRepository::getByIdOrThrowError($amendmnet_id);

        if($user->id != $amendmnet->user_id)
            throw new NotPermittedException('The Subamendment can only be accepted/rejected by the owner of the corresponding Amendment');
    }

    /**
     * Validates the Request and throws an Exception, if the request
     * is ambiguous
     * @return Null
     * @throws InvalidValueException
     */
    public function validate()
    {
        $validator = Validator::make($this->request->all(), [
            'accept' => 'required|boolean',
            'explanation' => 'required_if:accept,false|string|min:1|max:10000'
        ]);

        if($validator->fails())
            throw new InvalidValueException($validator->errors());
    }

    /**
     * Returns the data of the request,
     * keys are modified, so that they are autofillable
     * whenever possible
     *
     * @return array
     */
    public function getData()
    {
        return $this->request->all();
    }
}
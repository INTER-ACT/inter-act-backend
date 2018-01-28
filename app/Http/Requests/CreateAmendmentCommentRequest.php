<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use Auth;
use Illuminate\Http\Request;
use Validator;

class CreateAmendmentCommentRequest implements IRequest
{
    public $request;

    /**
     * CreateAmendmentCommentRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->validate();
        $this->authorize();
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
            'comment_text' => 'required|string|min:1|max:10000',
            'tags' => 'required|array',
            'tags.*' => 'required|integer|exists:tags,id|distinct'
        ]);

        if($validator->fails())
            throw new InvalidValueException($validator->errors());
    }

    /**
     * Throws an Exception if the User is not logged in
     */
    public function authorize()
    {
        if(!Auth::check())
            throw new NotAuthorizedException('You must be logged in to create a Comment.');
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->request->all();
        return [
            'content' => $data['comment_text'],
            'tags' => $data['tags']
        ];
    }
}
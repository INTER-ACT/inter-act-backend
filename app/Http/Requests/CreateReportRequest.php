<?php

namespace App\Http\Requests;


use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class CreateReportRequest implements IRequest
{
    public $request;

    /**
     * CreateReportRequest constructor.
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
     * @throws CannotResolveDependenciesException
     */
    public function validate()
    {
        $this->checkFields();
        $this->checkDependencies();
    }

    /**
     * @throws InvalidValueException
     */
    private function checkFields()
    {
        $validator = Validator::make($this->request->all(), [
            'reported_item' => [
                'required',
                Rule::in(['comment', 'amendment', 'subamendment'])
            ],
            'item_id' => 'required|numeric',
            'description' => 'required|string',
        ]);

        if($validator->fails())
            throw new InvalidValueException($validator->errors());
    }

    /**
     * @throws CannotResolveDependenciesException
     */
    private function checkDependencies()
    {
        $itemType = $this->request->all()['reported_item'];
        $itemId = $this->request->all()['item_id'];
        $item = Null;


        if($itemType == 'comment')
            $item = Comment::find($itemId);
        else if($itemType == 'amendment')
            $item = Amendment::find($itemId);
        else if($itemType == 'subamendment')
            $item = SubAmendment::find($itemId);

        if($item === Null)
            throw new CannotResolveDependenciesException("Cannot find $itemType with id $itemId");
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
        $data = [];
        $itemType = $this->request->all()['reported_item'];

        if($itemType == 'comment')
            $data['type'] = Comment::class;
        else if($itemType == 'amendment')
            $data['type'] = Amendment::class;
        else if($itemType == 'subamendment')
            $data['type'] = SubAmendment::class;

        $data['explanation'] = $this->request->all()['description'];
        $data['id'] = $this->request->all()['item_id'];

        return $data;
    }
}
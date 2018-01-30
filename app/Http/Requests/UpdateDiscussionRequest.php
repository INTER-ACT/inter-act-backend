<?php

namespace App\Http\Requests;

use App\Discussions\Discussion;
use App\Domain\ApiRequest;
use App\Domain\DiscussionRepository;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscussionRequest extends ApiRequest
{
    use RequestHasTagsTrait;

    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     * @throws NotPermittedException
     */
    public function authorize()
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($this->route('discussion_id'));
        if(!$this->user()->can('update', $discussion))
            throw new NotPermittedException('The logged in user is not permitted to create a discussion.');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'law_explanation' => 'string',
            'tags' => 'array|required',
            'tags.*' => 'integer|exists:tags,id|distinct'
        ];
    }
}

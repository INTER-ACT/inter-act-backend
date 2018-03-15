<?php

namespace App\Http\Requests;

use App\Discussions\Discussion;
use App\Domain\ApiRequest;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Exceptions\CustomExceptions\NotPermittedException;
use App\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateDiscussionRequest extends ApiRequest
{
    use RequestHasTagsTrait;

    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     * @throws NotPermittedException
     */
    public function authorize()
    {
        if(!$this->user()->can('create', Discussion::class))
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
            'title' => 'required|string|max:254',
            'law_number' => 'required|string|max:64',
            'law_text' => 'required|string',
            'law_explanation' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'integer|exists:tags,id|distinct'
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;

class CreateCommentRequest extends ApiRequest
{
    use RequestHasTagsTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'content' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'integer|exists:tags,id|distinct'
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Discussions\Discussion;
use App\Domain\ApiRequest;
use App\Exceptions\CustomExceptions\NotPermittedException;
use Illuminate\Foundation\Http\FormRequest;

class DeleteDiscussionRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('delete', Discussion::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}

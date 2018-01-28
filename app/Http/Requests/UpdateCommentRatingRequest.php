<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;

class UpdateCommentRatingRequest extends ApiRequest
{
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
            'user_rating' => 'required|integer'
        ];
    }
}

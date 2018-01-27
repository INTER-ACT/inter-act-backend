<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\MultiAspectRating;
use Illuminate\Foundation\Http\FormRequest;

class CreateMultiAspectRatingRequest extends ApiRequest
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
            MultiAspectRating::ASPECT1 => 'required|boolean',
            MultiAspectRating::ASPECT2 => 'required|boolean',
            MultiAspectRating::ASPECT3 => 'required|boolean',
            MultiAspectRating::ASPECT4 => 'required|boolean',
            MultiAspectRating::ASPECT5 => 'required|boolean',
            MultiAspectRating::ASPECT6 => 'required|boolean',
            MultiAspectRating::ASPECT7 => 'required|boolean',
            MultiAspectRating::ASPECT8 => 'required|boolean',
            MultiAspectRating::ASPECT9 => 'required|boolean',
            MultiAspectRating::ASPECT10 => 'required|boolean',
        ];
    }
}

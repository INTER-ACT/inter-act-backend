<?php

namespace App\Http\Requests;

use App\Amendments\Amendment;
use App\Domain\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateAmendmentRequest extends ApiRequest
{
    use RequestHasTagsTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;    //TODO: change to below? (guest is no user at all usually)
        return $this->user()->can('create', Amendment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'explanation' => 'required|string',
            'updated_text' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'integer|exists:tags,id|distinct'
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Discussions\Discussion;
use App\Role;
use Illuminate\Foundation\Http\FormRequest;

class CreateDiscussionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Discussion::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'law_id' => regex('NOR[1-9]{8}'),   //TODO: remove for safety regards?
            'law_explanation' => 'required',
            'tags' => 'required',
            'tags.*' => 'int'
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Discussions\Discussion;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscussionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() //TODO: just trying it out, not final
    {
        $user = $this->user();
        if($user === null or !($user instanceof User)) return false;
        $discussion = Discussion::find($this->route('discussion_id'));
        return $user->can('update', $discussion);
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

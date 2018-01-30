<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\Domain\DiscussionRepository;
use App\Domain\UserRepository;
use App\Role;

class ViewUserDetailsRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = UserRepository::getByIdOrThrowError($this->route('user_id'));
        return $this->user()->hasRole(Role::getAdmin()) || $this->user()->id == $user->id;
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

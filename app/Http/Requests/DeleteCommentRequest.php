<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\Role;

class DeleteCommentRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRole(Role::getAdmin());
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\Domain\DiscussionRepository;
use App\Exceptions\CustomExceptions\NotPermittedException;

class ViewDiscussionRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     * @throws NotPermittedException
     */
    public function authorize()
    {
        $discussion = DiscussionRepository::getDiscussionByIdOrThrowError($this->route('discussion_id'));
        if($discussion->isActive())
            return true;
        if(!$this->user()->can('view', $discussion))
            throw new NotPermittedException('The logged in user is not permitted to view this discussion (could be due to it being archived).');
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
            //
        ];
    }
}

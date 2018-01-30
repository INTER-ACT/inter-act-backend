<?php

namespace App\Http\Requests;

use App\Domain\ApiRequest;
use App\Domain\CommentRepository;
use App\Exceptions\CustomExceptions\NotPermittedException;

class UpdateCommentRequest extends ApiRequest
{
    use RequestHasTagsTrait;

    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     * @throws NotPermittedException
     */
    public function authorize()
    {
        $comment = CommentRepository::getCommentByIdOrThrowError($this->route('comment_id'));
        if(!$this->user()->can('update', $comment))
            throw new NotPermittedException('The logged in user is not permitted to create a discussion.');
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
            'tags' => 'required|array',
            'tags.*' => 'integer|exists:tags,id|distinct'
        ];
    }
}

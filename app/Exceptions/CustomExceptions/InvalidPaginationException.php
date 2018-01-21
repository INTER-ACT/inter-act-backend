<?php

namespace App\Exceptions\CustomExceptions;


class InvalidPaginationException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(400, "Request_02", "Bad Request");
        parent::__construct($meta, $details);
    }
}
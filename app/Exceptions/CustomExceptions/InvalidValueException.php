<?php

namespace App\Exceptions\CustomExceptions;


class InvalidValueException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(400, "Request_05", "Invalid Value");
        parent::__construct($meta, $details);
    }
}
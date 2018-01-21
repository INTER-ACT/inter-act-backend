<?php

namespace App\Exceptions\CustomExceptions;


class MissingArgumentException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(400, "Creation_01", "Missing Argument");
        parent::__construct($meta, $details);
    }
}
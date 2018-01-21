<?php

namespace App\Exceptions\CustomExceptions;


class ResourceNotFoundException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(404, "Request_01", "Resource Not Found");
        parent::__construct($meta, $details);
    }
}
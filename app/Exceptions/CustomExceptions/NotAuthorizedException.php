<?php

namespace App\Exceptions\CustomExceptions;


class NotAuthorizedException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(401, "Permission_01", "Not Authorized");
        parent::__construct($meta, $details);
    }
}
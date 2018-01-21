<?php

namespace App\Exceptions\CustomExceptions;


class LoginFailedException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(400, "Request_07", "Login Failed");
        parent::__construct($meta, $details);
    }
}
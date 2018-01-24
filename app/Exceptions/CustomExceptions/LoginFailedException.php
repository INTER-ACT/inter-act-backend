<?php

namespace App\Exceptions\CustomExceptions;


class LoginFailedException extends ApiException
{
    public const HTTP_CODE = 400;
    public const ERROR_CODE = "Request_07";

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Login Failed");
        parent::__construct($meta, $details);
    }
}
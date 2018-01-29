<?php

namespace App\Exceptions\CustomExceptions;


class NotAuthorizedException extends ApiException
{
    public const HTTP_CODE = 401;
    public const ERROR_CODE = 6;

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Not Authorized");
        parent::__construct($meta, $details);
    }
}
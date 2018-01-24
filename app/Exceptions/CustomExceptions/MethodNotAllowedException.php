<?php

namespace App\Exceptions\CustomExceptions;


class MethodNotAllowedException extends ApiException
{
    public const HTTP_CODE = 405;
    public const ERROR_CODE = "A_02";

    public function __construct($details = null)
    {
        $meta =  new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Method Not Allowed");
        parent::__construct($meta, $details);
    }
}
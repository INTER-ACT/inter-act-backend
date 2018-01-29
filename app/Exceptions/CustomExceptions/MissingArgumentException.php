<?php

namespace App\Exceptions\CustomExceptions;


class MissingArgumentException extends ApiException
{
    public const HTTP_CODE = 400;
    public const ERROR_CODE = 8;

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Missing Argument");
        parent::__construct($meta, $details);
    }
}
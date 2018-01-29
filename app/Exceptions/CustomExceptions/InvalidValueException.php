<?php

namespace App\Exceptions\CustomExceptions;


class InvalidValueException extends ApiException
{
    public const HTTP_CODE = 400;
    public const ERROR_CODE = 3;

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Invalid Value");
        parent::__construct($meta, $details);
    }
}
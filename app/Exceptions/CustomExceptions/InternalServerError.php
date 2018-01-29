<?php

namespace App\Exceptions\CustomExceptions;


class InternalServerError extends ApiException
{
    public const HTTP_CODE = 500;
    public const ERROR_CODE = 12;

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Internal Server Error");
        parent::__construct($meta, $details);
    }
}
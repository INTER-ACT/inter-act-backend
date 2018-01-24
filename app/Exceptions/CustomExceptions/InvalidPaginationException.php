<?php

namespace App\Exceptions\CustomExceptions;


class InvalidPaginationException extends ApiException
{
    public const HTTP_CODE = 400;
    public const ERROR_CODE = "Request_02";

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Bad Request");
        parent::__construct($meta, $details);
    }
}
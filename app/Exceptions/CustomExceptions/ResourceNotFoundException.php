<?php

namespace App\Exceptions\CustomExceptions;


class ResourceNotFoundException extends ApiException
{
    public const HTTP_CODE = 404;
    public const ERROR_CODE = "Request_01";

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Resource Not Found");
        parent::__construct($meta, $details);
    }
}
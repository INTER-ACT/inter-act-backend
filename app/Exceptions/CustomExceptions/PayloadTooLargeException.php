<?php

namespace App\Exceptions\CustomExceptions;


class PayloadTooLargeException extends ApiException
{
    public const HTTP_CODE = 413;
    public const ERROR_CODE = "Request_06";

    public function __construct($details = null)
    {
        $meta =  new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Payload Too Large");
        parent::__construct($meta, $details);
    }
}
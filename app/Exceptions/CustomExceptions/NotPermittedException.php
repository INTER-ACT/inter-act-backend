<?php

namespace App\Exceptions\CustomExceptions;


class NotPermittedException extends ApiException
{
    public const HTTP_CODE = 403;
    public const ERROR_CODE = "Permission_02";

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Not Permitted");
        parent::__construct($meta, $details);
    }
}
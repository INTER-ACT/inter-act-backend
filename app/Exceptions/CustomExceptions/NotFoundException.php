<?php

namespace App\Exceptions\CustomExceptions;


class NotFoundException extends ApiException
{
    public const HTTP_CODE = 404;
    public const ERROR_CODE = "A_01";

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Not Found");
        parent::__construct($meta, $details);
    }
}
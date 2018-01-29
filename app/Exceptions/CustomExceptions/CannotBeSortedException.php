<?php

namespace App\Exceptions\CustomExceptions;


class CannotBeSortedException extends ApiException
{
    public const HTTP_CODE = 400;
    public const ERROR_CODE = 2;

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Cannot Be Sorted");
        parent::__construct($meta, $details);
    }
}
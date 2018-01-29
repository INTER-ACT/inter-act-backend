<?php

namespace App\Exceptions\CustomExceptions;


class PaginationOutOfRangeException extends ApiException
{
    public const HTTP_CODE = 400;
    public const ERROR_CODE = 2;

    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Pagination Out Of Range");
        parent::__construct($meta, $details);
    }
}
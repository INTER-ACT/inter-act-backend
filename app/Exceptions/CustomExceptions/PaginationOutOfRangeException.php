<?php

namespace App\Exceptions\CustomExceptions;


class PaginationOutOfRangeException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(400, "Request_03", "Pagination Out Of Range");
        parent::__construct($meta, $details);
    }
}
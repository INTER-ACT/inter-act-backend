<?php

namespace App\Exceptions\CustomExceptions;


class CannotBeSortedException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(400, "Request_04", "Cannot Be Sorted");
        parent::__construct($meta, $details);
    }
}
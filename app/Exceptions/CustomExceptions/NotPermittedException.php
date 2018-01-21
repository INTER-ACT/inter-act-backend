<?php

namespace App\Exceptions\CustomExceptions;


class NotPermittedException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(403, "Permission_02", "Not Permitted");
        parent::__construct($meta, $details);
    }
}
<?php

namespace App\Exceptions\CustomExceptions;


class NotFoundException extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(404, "A_01", "Not Found");
        parent::__construct($meta, $details);
    }
}
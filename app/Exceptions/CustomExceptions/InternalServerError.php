<?php

namespace App\Exceptions\CustomExceptions;


class InternalServerError extends ApiException
{
    public function __construct($details = null)
    {
        $meta = new ApiExceptionMeta(500, "A_03", "Internal Server Error");
        parent::__construct($meta, $details);
    }
}
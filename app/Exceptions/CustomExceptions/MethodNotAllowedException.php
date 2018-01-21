<?php

namespace App\Exceptions\CustomExceptions;


class MethodNotAllowedException extends ApiException
{
    public function __construct($details = null)
    {
        $meta =  new ApiExceptionMeta(405, "A_02", "Method Not Allowed");
        parent::__construct($meta, $details);
    }
}
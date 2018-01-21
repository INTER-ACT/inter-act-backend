<?php

namespace App\Exceptions\CustomExceptions;


class PayloadTooLargeException extends ApiException
{
    public function __construct($details = null)
    {
        $meta =  new ApiExceptionMeta(413, "Request_06", "Payload Too Large");
        parent::__construct($meta, $details);
    }
}
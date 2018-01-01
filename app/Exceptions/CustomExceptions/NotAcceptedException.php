<?php

namespace App\Exceptions\CustomExceptions;


class NotAcceptedException extends \Exception
{
    public function __construct($message = "The SubAmendment hasn't been accepted yet!", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
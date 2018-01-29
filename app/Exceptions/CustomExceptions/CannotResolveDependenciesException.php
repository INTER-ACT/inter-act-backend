<?php

namespace App\Exceptions\CustomExceptions;


class CannotResolveDependenciesException extends ApiException
{
    public const HTTP_CODE = 404;
    public const ERROR_CODE = 9;

    public function __construct($details = null)
    {
        $meta =  new ApiExceptionMeta(self::HTTP_CODE, self::ERROR_CODE, "Cannot Resolve Dependencies");
        parent::__construct($meta, $details);
    }
}
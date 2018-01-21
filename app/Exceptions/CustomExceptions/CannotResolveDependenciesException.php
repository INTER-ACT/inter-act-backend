<?php

namespace App\Exceptions\CustomExceptions;


class CannotResolveDependenciesException extends ApiException
{
    public function __construct($details = null)
    {
        $meta =  new ApiExceptionMeta(404, "Creation_03", "Cannot Resolve Dependencies");
        parent::__construct($meta, $details);
    }
}
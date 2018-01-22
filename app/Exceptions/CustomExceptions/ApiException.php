<?php

namespace App\Exceptions\CustomExceptions;

class ApiExceptionMeta
{
    public static function getRequestResourceNotFound(){ return new ApiExceptionMeta(404, "Request_01", "Resource Not Found"); }
    public static function getRequestInvalidPagination(){ return new ApiExceptionMeta(400, "Request_02", "Bad Request"); }
    public static function getRequestPaginationOutOfRange(){ return new ApiExceptionMeta(400, "Request_03", "Pagination Out Of Range"); }
    public static function getRequestCannotBeSorted(){ return new ApiExceptionMeta(400, "Request_04", "Cannot Be Sorted"); }
    public static function getRequestInvalidValue(){ return new ApiExceptionMeta(400, "Request_05", "Invalid Value"); }
    public static function getRequestPayloadTooLarge(){ return new ApiExceptionMeta(413, "Request_06", "Payload Too Large"); }
    public static function getRequestLoginFailed(){ return new ApiExceptionMeta(400, "Request_07", "Login Failed"); }

    public static function getPermissionNotAuthorized(){ return new ApiExceptionMeta(401, "Permission_01", "Not Authorized"); }
    public static function getPermissionNotPermitted(){ return new ApiExceptionMeta(403, "Permission_02", "Not Permitted"); }

    public static function getCreationMissingArgument(){ return new ApiExceptionMeta(400, "Creation_01", "Missing Argument"); }
    public static function getCreationInvalidValue(){ return new ApiExceptionMeta(400, "Creation_02", "Not Authorized"); }
    public static function getCreationCannotResolveDependencies(){ return new ApiExceptionMeta(404, "Creation_03", "Cannot Resolve Dependencies"); }

    public static function getANotFound(){ return new ApiExceptionMeta(404, "A_01", "Not Found"); }
    public static function getAMethodNotAllowed(){ return new ApiExceptionMeta(405, "A_02", "Method Not Allowed"); }
    public static function getAInternalServerError(){ return new ApiExceptionMeta(500, "A_03", "Internal Server Error"); }
    public static function getAUnauthorized(){ return new ApiExceptionMeta(401, "A_04", "Unauthorized"); }

    protected $httpCode;
    protected $errorCode;
    protected $message;

    public function __construct(int $httpCode, string $errorCode, string $message)
    {
        $this->errorCode = $errorCode;
        $this->httpCode = $httpCode;
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}

class ApiException extends \Exception
{
    protected $httpCode;
    protected $code;
    protected $message;
    protected $details;

    public function __construct(ApiExceptionMeta $meta, string $details = null)
    {
        $this->httpCode = $meta->getHttpCode();
        $this->code = $meta->getErrorCode();
        $this->message = $meta->getMessage();
        $this->details = $details;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->apiError($this->httpCode, $this->code, $this->message, $this->details);
    }
}
<?php

namespace App\Exceptions;

use App\Exceptions\CustomExceptions\ApiException;
use App\Exceptions\CustomExceptions\ApiExceptionMeta;
use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     * @throws ApiException
     * @throws NotAuthorizedException
     */
    public function render($request, Exception $exception)
    {
        //TODO Change errors that have not been caught to Internal Server Error?
        //TODO make sure that the return type is always json (see commented code below)
        if($exception instanceof AuthenticationException)
            throw new NotAuthorizedException('The user is not authenticated');
        //if($exception instanceof NotFoundHttpException)
        //    throw new ResourceNotFoundException($exception->getTraceAsString());
        //if(!$exception instanceof ApiException and method_exists($exception, 'getStatusCode'))
        //    throw new ApiException(new ApiExceptionMeta($exception->getStatusCode(), $exception->getStatusCode(), $exception->getMessage()), $exception->getTraceAsString());
        //if($exception instanceof \ErrorException)
        //    throw new InternalServerError("The server could not resolve your request.");
        return parent::render($request, $exception);
    }
}

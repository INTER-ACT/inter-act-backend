<?php

namespace App\Exceptions;

use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Exceptions\CustomExceptions\NotAuthorizedException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //TODO: fill dontReport array if needed
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
     * @throws CannotResolveDependenciesException
     * @throws NotAuthorizedException
     */
    public function render($request, Exception $exception)
    {
        //TODO: Change errors that have not been caught to Internal Server Error?
        if($exception instanceof AuthenticationException)
            throw new NotAuthorizedException('The user is not authenticated');
        if($exception instanceof QueryException and $exception->getCode() == 23000) //TODO: is it safe like that?
            throw new CannotResolveDependenciesException($exception->getMessage());

        //if($exception instanceof \ErrorException)
        //    throw new InternalServerError("The server could not resolve your request.");
        return parent::render($request, $exception);
    }
}

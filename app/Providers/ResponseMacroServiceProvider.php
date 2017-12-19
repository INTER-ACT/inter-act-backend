<?php

namespace App\Providers;

use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(ResponseFactory $factory)
    {
        Response::macro('apiError', function(int $httpCode, string $code, string $message, string $details = null){
            return response()
                ->setStatusCode($httpCode)
                ->json([
                    'code' => $code,
                    'message' => $message,
                    'details' => $details
                ]);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

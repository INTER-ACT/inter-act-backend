<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        //<self-created-code>
        if(file_exists(dirname(__DIR__) . '/.env'))
        {
            (new \Dotenv\Dotenv(dirname(__DIR__), '.env'))->load();
        }
        //</self-created-code

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}

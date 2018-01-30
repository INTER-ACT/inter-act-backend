<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 30.01.18
 * Time: 16:35
 */

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait TestCreatesApplication
{
    public $envFilePath = '.env.test';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        //<self-created-code>
        if(file_exists(dirname(__DIR__) . '/' . $this->envFilePath))
        {
            (new \Dotenv\Dotenv(dirname(__DIR__), $this->envFilePath))->load();
        }
        //</self-created-code

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
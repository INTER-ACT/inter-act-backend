<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 26.01.18
 * Time: 11:29
 */

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Console\Kernel;

abstract class FeatureTestCase extends BaseTestCase
{
    use ApiTestTrait;

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
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

    const DEFAULT_PAGINATION_COUNT = 100; // Default value for items per page
    const MAX_PAGINATION_COUNT = 100;

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
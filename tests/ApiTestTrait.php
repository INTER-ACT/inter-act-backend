<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 22.01.18
 * Time: 13:45
 */

namespace Tests;


use Illuminate\Foundation\Testing\DatabaseMigrations;

trait ApiTestTrait
{
    use DatabaseMigrations;

    public function getUrl(string $path = '')
    {
        return config('app.url') . $path;
    }
}
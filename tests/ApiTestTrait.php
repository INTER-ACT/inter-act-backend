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
    protected $baseURI = 'http://localhost';
}
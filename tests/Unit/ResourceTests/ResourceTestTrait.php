<?php

namespace Tests\Unit\ResourceTests;


use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;

trait ResourceTestTrait
{
    use WithoutMiddleware;
    use DatabaseMigrations;
    protected $baseURI = 'http://localhost';

}
<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTests extends TestCase
{
    use DatabaseMigrations;

    public function testPing()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $response = $this->get('/ping');

        $response->assertStatus(200);
    }

    // TODO write manuel tests as well
}

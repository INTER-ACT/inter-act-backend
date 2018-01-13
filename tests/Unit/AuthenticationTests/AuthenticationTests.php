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

    public function testPingWithAuthentication()
    {
        Passport::actingAs(
            factory(User::class)->create(), ['*']
        );

        $response = $this->get('/ping');

        $response->assertStatus(200);
    }

    public function testPingWithoutAuthentication(){
        $response = $this->get('/ping');

        $response->assertStatus(401);
    }

    // TODO write manuel tests as well

    public function testLogin(){
        $password = 'password1234';

        $user = factory(User::class)->create([
            'password' => $password
        ]);

        $response = $this->get('/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => '1',
                'client_secret' => 'Z65vhodmvnA1SiHhiuPiML3ndaO30OwKV3O7A1TV',
                'email' =>  $user->email,
                'password' => $password,
                'scope' => '',
            ]
        ]);
    }

    public function testLogout(){
        $password = 'password1234';

        $user = factory(User::class)->create([
            'password' => $password
        ]);

        $response = $this->delete('/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => '1',
                'client_secret' => 'Z65vhodmvnA1SiHhiuPiML3ndaO30OwKV3O7A1TV',
                'email' =>  $user->email,
                'password' => $password,
                'scope' => '',
            ]
        ]);
    }
}

<?php

namespace Tests\Unit;

use App\Role;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class UserTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testUserResource()
    {
        $user = factory(User::class)->create();
        $role = Role::find($user->role_id);

        $resourcePath = $this->baseURI . $user->getResourcePath();
        $response = $this->get($resourcePath);

        $response->assertJson([
            'href' => $resourcePath,
            'id' => $user->id,
            'username' => $user->username,
            'role' => $role->name,

            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'postal_code' => $user->postal_code,
            'residence' => $user->city,
            'job' => $user->job,
            'highest_education' => $user->graduation
        ]);
    }

    /** @test */
    public function testUserCollection()
    {
        $_ = factory(User::class, 3)->create();

        $users = User::all();

        $resourcePath = $this->baseURI . "/users";
        $response = $this->get($resourcePath);

        $users_json = [];
        foreach($users as $user){
            $users_json[] = [
                'href' => $this->baseURI . $user->getResourcePath(),
                'id' => $user->id,
                'username' => $user->username
            ];
        }

        $response->assertJson([
            'href' => $resourcePath,
            'total' => count($users),
            'users' => $users_json
        ]);
    }
}

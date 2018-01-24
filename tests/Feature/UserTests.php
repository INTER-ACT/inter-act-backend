<?php

namespace Tests\Feature;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use PHPUnit\ExampleExtension\TestCaseTrait;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTests extends TestCase
{
    use DatabaseMigrations;

    // GET /users
    public function testGettingUsersStatus()
    {
        factory(User::class, 5)->create();

        $response = $this->get('/users');

        $response->assertStatus(200);
    }

    public function testPaginationOfUsers()
    {

    }

    // POST /users

    public function testCreationOfUserStatus()
    {
        $userData = [
            'username' => 'TestUser00',
            'email' => 'me12@me.com',
            'password' => 'abcd123!',
            'first_name' => 'Hans',
            'last_name' => 'Nachname',
            'sex' => 'm',
            'postal_code' => 3500,
            'residence' => 'Krems',
            'job' => 'Maurer',
            'highest_education' => 2,       //TODO use actual data, as soon as the graduation levels are known
            'year_of_birth' => 1996
        ];

        $response = $this->post('/users', $userData);

        $response->assertStatus(201);
    }

    // GET /users/{id}

    public function testGettingUserAsGuestStatus()
    {
        $user = factory(User::class)->create();

        $response = $this->get($user->getResourcePath());

        $response->assertStatus(200);
    }

    // PATCH /users/{id}

    /**
     * Tests that the authenticated user can update his properties
     */
    public function testUpdatingUserStatus()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->patch($user->getResourcePath(),[
            'email' => 'mymail@me.com',
            'password' => 'abcc123!',
            'last_name' => 'Santana',
            'residence' => 'Wien',
            'job' => 'Anwalt',
            'highest_education' => 3
        ]);

        $response->assertStatus(204);
    }

    /**
     * Tests that the authenticated user can delete his account
     */
    public function testDeleteUserStatus()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->delete($user->getResourcePath());

        $response->assertStatus(204);
    }

    /**
     * Tests that an authenticated Admin can change a user's role
     */
    public function testChangeUserRoleStatus()
    {
        $admin = factory(User::class)->states('admin')->create();
        $user = factory(User::class)->create();

        Passport::actingAs($admin);

        $response = $this->put($user->getResourcePath() . '/role', [
            'role' => 'admin'
        ]);

        $response->assertStatus(204);
    }

    /**
     * Tests that a user can get the amendments of a user
     */
    public function testGetAmendmentsStatus()
    {
        $user = factory(User::class)->create();
        $amendments = factory(Amendment::class, 5)->states('discussion')->create([
            'user_id' => $user->id
        ]);

        $response = $this->get($user->getResourcePath() . '/amendments');

        $response->assertStatus(200);
    }

    /**
     * Tests that a user can get the subamendments of a user
     */
    public function testGetSubAmendmentsStatus()
    {
        $user = factory(User::class)->create();
        $subamendments = factory(SubAMendment::class, 5)->states('amendment')->create([
            'user_id' => $user->id
        ]);

        $response = $this->get($user->getResourcePath() . '/subamendments');

        $response->assertStatus(200);
    }

    /**
     * Tests that a user can get the comments of a user
     */
    public function testGetCommentsStatus()
    {
        $user = factory(User::class)->create();

        // TODO test discussion comments as well

        $comments = factory(Comment::class, 5)->states('amendment')->create([
            'user_id'=> $user->id
        ]);

        $response = $this->get($user->getResourcePath() . '/comments');

        $response->assertStatus(200);
    }

    /**
     * Tests that a user can get the discussions of a user
     */
    public function testGetDiscussionsStatus()
    {
        $user = factory(User::class)->states('admin')->create();

        $discussions = factory(Discussion::class, 5)->create([
            'user_id' => $user->id
        ]);

        $response = $this->get($user->getResourcePath() . '/discussions');

        $response->assertStatus(200);
    }

    /**
     * Tests that a user can get the discussions of a user
     */
    public function testGetStatisticsStatus()
    {
        $user = factory(User::class)->create();

        $response = $this->get($user->getResourcePath() . '/statistics');
        $response->assertStatus(200);
    }

}

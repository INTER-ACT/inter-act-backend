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

    // POST /users

    /**
     * Tests that the creation of a user returns the correct response
     */
    public function testCreationOfUserResponse()
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

        $data = (array)json_decode($response->content());
        $this->assertArrayHasKey('href', $data);
        $this->assertArrayHasKey('id', $data);
    }

    /**
     * Tests that the user and all his values are saved to the database,
     * when creating the user
     */
    public function testCreatedUserIsInDatabase()
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

        $data = (array)json_decode($response->content());
        $userId = $data['id'];

        $user = User::find($userId);

        $this->assertNotNull($user, 'The does not exist in the database');

        $this->assertEquals($userData['username'], $user->username);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertNotEquals($userData['password'], $user->password);
        $this->assertEquals($userData['first_name'], $user->first_name);
        $this->assertEquals($userData['last_name'], $user->last_name);
        $this->assertEquals($userData['sex'] == 'm', $user->is_male);
        $this->assertEquals($userData['postal_code'], $user->postal_code);
        $this->assertEquals($userData['residence'], $user->city);
        $this->assertEquals($userData['job'], $user->job);
        $this->assertEquals($userData['highest_education'], $user->graduation);
        $this->assertEquals($userData['year_of_birth'], $user->year_of_birth);
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
     * Tests that authentication is required to update a User
     */
    public function testUpdatingUserWithoutAuthentication()
    {
        $user = factory(User::class)->create();

        $response = $this->patch($user->getResourcePath(),[
            'email' => 'mymail@me.com',
            'password' => 'abcc123!',
            'last_name' => 'Santana',
            'residence' => 'Wien',
            'job' => 'Anwalt',
            'highest_education' => 3
        ]);

        $response->assertStatus(401);
    }

    /**
     * Tests that a User cannot update another User's properties
     */
    public function testUpdatingUserAsAnotherUser()
    {
        $user = factory(User::class)->create();
        $anotherUser = factory(User::class)->create();

        Passport::actingAs($anotherUser);

        $response = $this->patch($user->getResourcePath(),[
            'email' => 'mymail@me.com',
            'password' => 'abcc123!',
            'last_name' => 'Santana',
            'residence' => 'Wien',
            'job' => 'Anwalt',
            'highest_education' => 3
        ]);

        $response->assertStatus(403);
    }


    // DELETE /users/{id}
    /**
     * Tests that the authenticated user can delete his account
     */
    public function testDeleteUserAsUser()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->delete($user->getResourcePath());

        $response->assertStatus(204);
    }

    /**
     * Tests that the authenticated admin can delete a User's account
     */
    public function testDeleteUserAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class)->states('admin')->create();

        Passport::actingAs($admin);

        $response = $this->delete($user->getResourcePath());

        $response->assertStatus(204);
    }

    /**
     * Tests that a authenticated User cannot delete another User's account
     */
    public function testDeleteUserAsAnotherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        Passport::actingAs($otherUser);

        $response = $this->delete($user->getResourcePath());

        $response->assertStatus(403);
    }

    /**
     * Tests that authentication is required in order to delete a User
     */
    public function testDeleteUserWithoutAuthentication()
    {
        $user = factory(User::class)->create();

        $response = $this->delete($user->getResourcePath());

        $response->assertStatus(401);
    }


    /**
     * Tests that the user is deleted after the authenticated user deleted his account
     */
    public function testUserIsDeleted()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->delete($user->getResourcePath());

        $deletedUser = User::find($user->id);

        $this->assertNull($deletedUser);
    }

    // GET /users/{id}/details

    /**
     * Tests that an authenticated User can see his Detailed User Description
     */
    public function testGetUserDetails()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get($user->getResourcePath() . '/details');

        $response->assertStatus(200);
    }

    /**
     * Tests that authentication is required in order to get details of a User
     */
    public function testGetUserDetailsWithoutAuthentication()
    {
        $user = factory(User::class)->create();

        $response = $this->get($user->getResourcePath() . '/details');

        $response->assertStatus(401);
    }

    /**
     * Tests that a User cannot get details of another User
     */
    public function testGetUserDetailsAsAnotherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        Passport::actingAs($otherUser);

        $response = $this->get($user->getResourcePath() . '/details');

        $response->assertStatus(403);
    }

    // PUT /users/{id}/role
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

    // GET /users/{id}/amendments
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

    // GET /users/{id}/subamendments
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

    // GET /users/{id}/amendment
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

    // GET /users/{id}/discussions
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

    // GET /users/{id}/statistics
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

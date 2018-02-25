<?php

namespace Tests\Feature;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Domain\Manipulators\UserManipulator;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Exceptions\CustomExceptions\ResourceNotFoundException;
use App\Http\Resources\UserResources\ShortUserResource;
use App\Http\Resources\UserResources\UserResource;
use App\PendingUser;
use App\User;
use Hash;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Tests\FeatureTestCase;
use Tests\TestCase;

class UserTests extends FeatureTestCase
{
    use DatabaseMigrations;

    //region GET /users
    public function testGettingUsersStatus()
    {
        factory(User::class, 5)->create();

        $response = $this->get('/users');

        $response->assertStatus(200);
    }
    //endregion

    //region POST /users

    /**
     * Tests that the creation of a user returns the correct response
     */
    public function testCreationOfUserResponse()
    {
        $username = 'Zederick_';
        $userData = [
            'username' => $username,
            'email' => 'leazedev@gmail.com',
            'password' => 'abcdefg123!',
            'first_name' => 'Hans',
            'last_name' => 'Wurst',
            'sex' => 'm',
            'postal_code' => 3500,
            'residence' => 'Pfaffings',
            'job' => 'Schüler',
            'highest_education' => "Höhere Technische Lehranstalt",
            'year_of_birth' => 1999
        ];

        $requestPath = $this->getUrl('/users');
        $response = $this->post($requestPath, $userData);
        $response->assertStatus(202);
    }

    /**
     * Tests that the creation of a user returns the correct response
     */
    public function testCreationOfUserAndValidation()
    {
        $username = 'Zederick_';
        $userData = [
            'username' => $username,
            'email' => 'leazedev@gmail.com',
            'password' => 'abcd123!',
            'first_name' => 'Hans',
            'last_name' => 'Wurst',
            'sex' => 'm',
            'postal_code' => 3500,
            'residence' => 'Pfaffings',
            'job' => 'Schüler',
            'highest_education' => "Höhere Technische Lehranstalt",
            'year_of_birth' => 1999
        ];
        $pendingUser = UserManipulator::create($userData);
        $token = $pendingUser->validation_token;
        $requestPath = $this->getUrl('/users/1');
        $user = $pendingUser->getUser();
        $user->id = 1;
        $response = $this->json('GET', $requestPath);
        $response->assertStatus(NotFoundException::HTTP_CODE)->assertJson(['code' => NotFoundException::ERROR_CODE]);
        $verificationPath = PendingUser::getVerificationUrlForToken($token);
        $verificationResponse = $this->json('GET', $verificationPath);
        $verificationResponse->assertStatus(302);   //redirect
        $requestPath = $this->getUrl('/users/1');
        $response = $this->get($requestPath);
        $response->assertStatus(200)
           ->assertJson((new ShortUserResource($user))->toArray(new Request()));
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
            'password' => 'abcdefg123!',
            'first_name' => 'Hans',
            'last_name' => 'Nachname',
            'sex' => 'm',
            'postal_code' => 3500,
            'residence' => 'Krems',
            'job' => 'Maurer',
            'highest_education' => "Höhere Technische Lehranstalt",
            'year_of_birth' => 1996
        ];

        $response = $this->post('/users', $userData);
        $response->assertStatus(202);
        //already tested if in database in previous test
        /*
        $userId = 1;
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
        $this->assertEquals($userData['year_of_birth'], $user->year_of_birth);*/
    }
    //endregion

    //region GET /users/{id}

    public function testGettingUserAsGuestStatus()
    {
        $user = factory(User::class)->create();

        $response = $this->get($user->getResourcePath());

        $response->assertStatus(200);
    }
    //endregion

    //region PATCH /users/{id}

    /**
     * Tests that the authenticated user can update his properties,
     * and that the values are changed accordingly
     *
     * With correct data only
     */
    public function testUpdatingUserAndValidate()
    {
        $firstPassword = '123!xyzzzz';
        $user = factory(User::class)->create([
            'password' => Hash::make($firstPassword)
        ]);

        $newMail = 'nonexistent@nonexistent.com';
        $newPassword = 'abcdefg123!';
        $newLastName = 'Santana';
        $newResidence = 'Wien';
        $newJob = 'Dunno';
        $newHighestEducation = 'Forschungszentrum für Bildungsbekämpfung, Krems';

        Passport::actingAs($user);

        $response = $this->patch($user->getResourcePath(), [
            'email' => $newMail,
            'password' => $newPassword,
            'old_password' => $firstPassword,
            'last_name' => $newLastName,
            'residence' => $newResidence,
            'job' => $newJob,
            'highest_education' => $newHighestEducation
        ]);

        $response->assertStatus(204);
        /** @var User $updatedUser */
        $updatedUser = User::find($user->id);
        self::assertNotNull($updatedUser);

        self::assertEquals($newMail, $updatedUser->email);
        self::assertEquals($newLastName, $updatedUser->last_name);
        self::assertEquals($newResidence, $updatedUser->city);
        self::assertEquals($newJob, $updatedUser->job);
        self::assertEquals($newHighestEducation, $updatedUser->graduation);
        self::assertFalse(Hash::check($newPassword, $updatedUser->password));
        $requestPath = $updatedUser->getVerificationUrl();
        $response = $this->get($requestPath);
        $response->assertStatus(302);
        $pwUpdatedUser = User::find($user->id);
        self::assertTrue(Hash::check($newPassword, $pwUpdatedUser->password));
    }

    public function testUpdatingUser()
    {
        $firstPassword = '123!xyzzzz';
        $user = factory(User::class)->create([
            'password' => Hash::make($firstPassword)
        ]);

        $newMail = 'leazedev@gmail.com';
        $newPassword = 'abcdefg123!';
        $newLastName = 'Santana';
        $newResidence = 'Wien';
        $newJob = 'Dunno';
        $newHighestEducation = 'Forschungszentrum für Bildungsbekämpfung, Krems';

        Passport::actingAs($user);

        $response = $this->patch($user->getResourcePath(), [
            'email' => $newMail,
            'password' => $newPassword,
            'old_password' => $firstPassword,
            'last_name' => $newLastName,
            'residence' => $newResidence,
            'job' => $newJob,
            'highest_education' => $newHighestEducation
        ]);

        $response->assertStatus(204);
    }

    public function testUpdatingUserWithInvalidOldPassword()
    {
        $firstPassword = '123!xyzz';
        $user = factory(User::class)->create([
            'password' => Hash::make($firstPassword)
        ]);

        $newMail = 'leazedev@gmail.com';
        $newPassword = 'abcc123!';
        $newLastName = 'Santana';
        $newResidence = 'Wien';
        $newJob = 'Dunno';
        $newHighestEducation = 'Forschungszentrum für Bildungsbekämpfung, Krems';

        Passport::actingAs($user);

        $response = $this->patch($user->getResourcePath(), [
            'email' => $newMail,
            'password' => $newPassword,
            'old_password' => 'notfirstpassword',
            'last_name' => $newLastName,
            'residence' => $newResidence,
            'job' => $newJob,
            'highest_education' => $newHighestEducation
        ]);

        $response->assertStatus(InvalidValueException::HTTP_CODE)->assertJson(['code' => InvalidValueException::ERROR_CODE]);
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

    /**
     * Tests that a User cannot change his password without providing his old one
     */
    public function testUpdatingPasswordWithoutOldPassword()
    {
        $user = factory(User::class)->create();

        $newPassword = 'abcc123!';

        Passport::actingAs($user);

        $response = $this->patch($user->getResourcePath(), [
            'password' => $newPassword,
        ]);

        $response->assertStatus(400);
    }

    /**
     * Tests that a User cannot change his password with a wrong old password
     */
    public function testUpdatingPasswordWithWrongOldPassword()
    {
        $firstPassword = '123!xyzz';
        $user = factory(User::class)->create([
            'password' => $firstPassword
        ]);

        $newPassword = 'abcc123!';

        Passport::actingAs($user);

        $response = $this->patch($user->getResourcePath(), [
            'password' => $newPassword,
            'old_password' => $firstPassword . '!',
        ]);

        $response->assertStatus(400);
    }
    //endregion

    //region DELETE /users/{id}
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
    //endregion

    //region PUT /users/{id}/role
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
    //endregion

    //region GET /users/{id}/amendments
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
    //endregion

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

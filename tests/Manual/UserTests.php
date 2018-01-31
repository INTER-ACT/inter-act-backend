<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 31.01.18
 * Time: 11:08
 */

namespace Tests\Manual;

use App\Domain\Manipulators\UserManipulator;
use App\Exceptions\CustomExceptions\NotFoundException;
use App\Http\Resources\UserResources\ShortUserResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\FeatureTestCase;

class UserTests extends FeatureTestCase
{
    use DatabaseMigrations;

    /**
     * Tests that the creation of a user returns the correct response
     */
    public function testCreationOfUserAndValidationManually()
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
            'highest_education' => 2,
            'year_of_birth' => 1999
        ];
        $pendingUser = UserManipulator::create($userData);
        $token = $pendingUser->validation_token;
        $requestPath = $this->getUrl('/users/1');
        $user = $pendingUser->getUser();
        $user->id = 1;
        $response = $this->get($requestPath);
        $response->assertStatus(NotFoundException::HTTP_CODE)->assertJson(['code' => NotFoundException::ERROR_CODE]);
        //verify sent email manually
        //application has to be served for this to work
        //gotta be fast
        sleep(15);
        $requestPath = $this->getUrl('/users/1');
        $user = $pendingUser->getUser();
        $user->id = 1;
        $response = $this->get($requestPath);
        $response->assertStatus(200)
            ->assertJson((new ShortUserResource($user))->toArray(new Request()));
    }
}
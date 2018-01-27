<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.01.18
 * Time: 09:57
 */

namespace Tests\Unit;


use App\Model\ModelFactory;
use App\MultiAspectRating;
use App\Role;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Unit\ResourceTests\ResourceTestTrait;

class MultiAspectRatingResourceTests extends TestCase
{
    use ResourceTestTrait;

    /** @test */
    public function testMultiAspectRatingResource()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()), ['*']);
        $discussion = ModelFactory::CreateDiscussion(\Auth::user());
        $other_rating = ModelFactory::CreateMultiAspectRating(ModelFactory::CreateUser(Role::getStandardUser()), $discussion);
        $user_rating = ModelFactory::CreateMultiAspectRating(\Auth::user(), $discussion);
        $total_rating = [MultiAspectRating::ASPECT1 => 0, MultiAspectRating::ASPECT2 => 0, MultiAspectRating::ASPECT3 => 0, MultiAspectRating::ASPECT4 => 0, MultiAspectRating::ASPECT5 => 0, MultiAspectRating::ASPECT6 => 0, MultiAspectRating::ASPECT7 => 0, MultiAspectRating::ASPECT8 => 0, MultiAspectRating::ASPECT9 => 0, MultiAspectRating::ASPECT10 => 0];
        $user_rating = $this->getArrayFromRating($user_rating);
        $other_rating = $this->getArrayFromRating($other_rating);
        foreach($user_rating as $key => $item)
        {
            if($user_rating[$key])
                $total_rating[$key]++;
            if($other_rating[$key])
                $total_rating[$key]++;
        }

        $requestPath = $this->getUrl($discussion->getRatingPath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)->assertJson([
            'href' => $requestPath,
            'user_rating' => $user_rating,
            'total_rating' => $total_rating
        ]);
    }

    /** @test */
    public function testMultiAspectRatingResourceNotAuthenticated()
    {
        $user = ModelFactory::CreateUser(Role::getAdmin());
        $discussion = ModelFactory::CreateDiscussion($user);
        $other_rating = ModelFactory::CreateMultiAspectRating(ModelFactory::CreateUser(Role::getStandardUser()), $discussion);
        $user_rating = ModelFactory::CreateMultiAspectRating($user, $discussion);
        $total_rating = [MultiAspectRating::ASPECT1 => 0, MultiAspectRating::ASPECT2 => 0, MultiAspectRating::ASPECT3 => 0, MultiAspectRating::ASPECT4 => 0, MultiAspectRating::ASPECT5 => 0, MultiAspectRating::ASPECT6 => 0, MultiAspectRating::ASPECT7 => 0, MultiAspectRating::ASPECT8 => 0, MultiAspectRating::ASPECT9 => 0, MultiAspectRating::ASPECT10 => 0];
        $user_rating = $this->getArrayFromRating($user_rating);
        $other_rating = $this->getArrayFromRating($other_rating);
        foreach($user_rating as $key => $item)
        {
            if($user_rating[$key])
                $total_rating[$key]++;
            if($other_rating[$key])
                $total_rating[$key]++;
        }

        $requestPath = $this->getUrl($discussion->getRatingPath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)->assertJson([
            'href' => $requestPath,
            'total_rating' => $total_rating
        ]);
    }

    /** @test */
    public function testMultiAspectRatingResourceNoRatings()
    {
        Passport::actingAs(ModelFactory::CreateUser(Role::getAdmin()), ['*']);
        $discussion = ModelFactory::CreateDiscussion(\Auth::user());
        $total_rating = [MultiAspectRating::ASPECT1 => 0, MultiAspectRating::ASPECT2 => 0, MultiAspectRating::ASPECT3 => 0, MultiAspectRating::ASPECT4 => 0, MultiAspectRating::ASPECT5 => 0, MultiAspectRating::ASPECT6 => 0, MultiAspectRating::ASPECT7 => 0, MultiAspectRating::ASPECT8 => 0, MultiAspectRating::ASPECT9 => 0, MultiAspectRating::ASPECT10 => 0];

        $requestPath = $this->getUrl($discussion->getRatingPath());
        $response = $this->get($requestPath);
        $response->assertStatus(200)->assertJson([
            'href' => $requestPath,
            'total_rating' => $total_rating,
            'user_rating' => MultiAspectRating::getEmptyRatingArray()
        ]);
    }

    /**
     * @param MultiAspectRating $rating
     * @return array
     */
    public static function getArrayFromRating(MultiAspectRating $rating) : array
    {
        $rating = $rating->toArray();
        unset($rating['id']);
        $rating = array_map(function($item){
            return (int)$item;
        }, $rating);
        return $rating;
    }
}
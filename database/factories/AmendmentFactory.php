<?php

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Tags\Tag;
use App\User;
use Faker\Generator as Faker;

/**
 * Factory for an Amendment
 *
 * User and Discussion ids must by substituted
 */
$factory->define(\App\Amendments\Amendment::class, function (Faker $faker) {
    return [
        'explanation' => "Test Explanation of AmendmentResource",
        'updated_text' => "Modified law_text ..."
    ];
});


/**
 * Factory for an Amendment
 *
 * Creates a User along with the amendment
 *
 */
$factory->state(\App\Amendments\Amendment::class, 'user', function (Faker $faker) {
    $user = factory(User::class)->create();
    return [
        'user_id' => $user->id,
    ];
});

/**
 * Factory for an Amendment
 *
 * Creates a Discussion along with the amendment
 *
 */
$factory->state(\App\Amendments\Amendment::class, 'discussion', function (Faker $faker) {
    $discussion = factory(Discussion::class)->states('user')->create();
    return [
        'discussion_id' => $discussion->id,
    ];
});
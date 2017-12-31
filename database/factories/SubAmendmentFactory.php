<?php

use App\Amendments\Amendment;
use App\User;
use Faker\Generator as Faker;

$factory->define(\App\Amendments\SubAmendment::class, function (Faker $faker) {
    return [
        'updated_text' => 'SubAmendment Updated Text: ' . $faker->paragraph(2),
        'explanation' => "Test Explanation of SubAmendment"
    ];
});

/**
 * Factory for a subamendment,
 * owned by a new user
 */
$factory->state(\App\Amendments\SubAmendment::class, 'user', function (Faker $faker) {
    $user = factory(User::class)->create();
    return [
        'user_id' => $user->id,
    ];
});

/**
 * Factory for a subamendment,
 * created for a new amendment (in a new discussion); both created by different, new users
 */
$factory->state(\App\Amendments\SubAmendment::class, 'amendment', function (Faker $faker) {
    $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
    return [
        'amendment_id' => $amendment->id,
    ];
});

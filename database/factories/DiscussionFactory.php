<?php

use App\User;
use Faker\Generator as Faker;

$factory->define(\App\Discussions\Discussion::class, function (Faker $faker) {
    return [
        'title' => $faker->text(100),
        'law_text' => $faker->paragraph(3),
        'law_explanation' => $faker->paragraph(5)
    ];
});

/**
 * Factory for a Discussion
 * Creates a user along with the discussion
 */
$factory->state(\App\Discussions\Discussion::class, 'user', function (Faker $faker) {
    $user = factory(User::class)->create();

    return [
        'user_id' => $user->id
    ];
});

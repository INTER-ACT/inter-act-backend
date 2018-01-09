<?php

use App\Amendments\Amendment;
use App\User;
use Faker\Generator as Faker;

$factory->define(\App\Comments\Comment::class, function (Faker $faker) {
    //$user = \App\User::First();
    //$parent = \App\Discussions\Discussion::First();
    return [
        //'commentable_id' => $parent->id,
        //'commentable_type' => \App\Discussions\Discussion::class,
        //'user_id' => $user->id,
        'content' => $faker->paragraph(2)
    ];
});


/**
 * Factory for a Comment with a new user
 *
 */
$factory->state(\App\Comments\Comment::class, 'user', function (Faker $faker) {
    $user = factory(User::class)->create();
    return [
        'user_id' => $user->id,
    ];
});

/**
 * Factory for a Comment on a new amendment
 *
 */
$factory->state(\App\Comments\Comment::class, 'amendment', function (Faker $faker) {
    $amendment =  factory(Amendment::class)->states('user', 'discussion')->create();
    return [
        'commentable_id' => $amendment->id,
        'commentable_type' => Amendment::class
    ];
});
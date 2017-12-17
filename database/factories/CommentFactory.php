<?php

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

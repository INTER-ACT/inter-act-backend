<?php

use Faker\Generator as Faker;

$factory->define(\App\Amendments\SubAmendment::class, function (Faker $faker) {
    //$user = \App\User::First();
    //$amendment = \App\Amendments\Amendment::First();
    return [
        //'amendment_id' => $amendment->id,
        //'user_id' => $user->id,
        'updated_text' => 'SubAmendment Updated Text: ' . $faker->paragraph(2),
        'explanation' => "Test Explanation of SubAmendment"
    ];
});

<?php

use Faker\Generator as Faker;

$factory->define(\App\Amendments\Amendment::class, function (Faker $faker) {
    $user = \App\User::First();
    $discussion = \App\Discussions\Discussion::First();
    return [
        'discussion_id' => $discussion->id,
        'user_id' => $user->id,
        'updated_text' => $discussion->law_text . 'Amendment Additional Text',
        'explanation' => "Test Explanation of Amendment"
    ];
});

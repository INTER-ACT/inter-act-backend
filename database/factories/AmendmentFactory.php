<?php

use App\Discussions\Discussion;
use App\User;
use Faker\Generator as Faker;

$factory->define(\App\Amendments\Amendment::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    $discussion = factory(Discussion::class)->create(['user_id' => $user->id]);
    return [
        'discussion_id' => $discussion->id,
        'user_id' => $user->id,
        'updated_text' => $discussion->law_text . 'AmendmentResource Additional Text',
        'explanation' => "Test Explanation of AmendmentResource"
    ];
});

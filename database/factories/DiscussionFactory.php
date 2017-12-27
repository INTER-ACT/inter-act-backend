<?php

use Faker\Generator as Faker;

$factory->define(\App\Discussions\Discussion::class, function (Faker $faker) {
    return [
        'title' => $faker->text(100),
        'law_text' => $faker->paragraph(3),
        'law_explanation' => $faker->paragraph(5)
    ];
});

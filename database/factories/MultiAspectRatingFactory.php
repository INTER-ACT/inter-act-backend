<?php

use App\MultiAspectRating;
use Faker\Generator as Faker;

$factory->define(MultiAspectRating::class, function (Faker $faker) {
    return [
        'aspect1' => $faker->boolean(),
        'aspect2' => $faker->boolean(),
        'aspect3' => $faker->boolean(),
        'aspect4' => $faker->boolean(),
        'aspect5' => $faker->boolean(),
        'aspect6' => $faker->boolean(),
        'aspect7' => $faker->boolean(),
        'aspect8' => $faker->boolean(),
        'aspect9' => $faker->boolean(),
        'aspect10' => $faker->boolean(),
    ];
});

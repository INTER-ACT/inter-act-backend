<?php

use Faker\Generator as Faker;

$factory->define(\App\Reports\Report::class, function (Faker $faker) {
    return [
        'explanation' => 'Test Explanation of Report: ' . $faker->paragraph(2)
    ];
});

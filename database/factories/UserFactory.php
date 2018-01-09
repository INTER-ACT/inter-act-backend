<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\User::class, function (Faker $faker) {
    static $password;
    $gender = $faker->randomElements(['male', 'female']);
    $gender_boolean = $gender == 'male';
    $postal_code = rand(1000, 9999);
    $graduation = $faker->randomElement(['HTL Krems','Vorschule Langenlois','ABC Schule Hinterhofingen']);

    return [
        'role_id' => \App\Role::getStandardUser()->id,
        'username' => $faker->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'first_name' => $faker->firstName($gender),
        'last_name' => $faker->lastName,
        'is_male' => $gender_boolean,
        'postal_code' => $postal_code,
        'city' => $faker->city,
        'job' => $faker->jobTitle,
        'graduation' => $graduation,
        'year_of_birth' => $faker->year,
        'remember_token' => str_random(10),
    ];
});
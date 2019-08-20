<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
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

$factory->define(User::class, function (Faker $faker) {
    $days = random_int(6570, 18300);
    $lga = random_int(1, 500);
    $genders = ["male", "female"];
    $maritalStatus = ["married", "single", "divorced", "widowed"];
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'lga_id' => $lga,
        'address1' => $faker->address,
        'address2' => $faker->address,
        'dob' => Carbon::now()->subDays($days),
        "gender" => $genders[random_int(0, 1)],
        'phone_number' => $faker->phoneNumber,
        "marital_status" => $maritalStatus[random_int(0, 3)],
        "occupation" => $faker->jobTitle,
        "picture" => "profile-picture/" . random_int(1, 800) . ".jpg",
        'roles' => json_encode(["voter"]),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

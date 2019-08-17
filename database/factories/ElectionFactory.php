<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(\App\Election::class, function (Faker $faker) {
    $random = random_int(0, 1);
    $status = ["pending", "ongoing"];
    $date = $faker->dateTime;
    return [
        "name" => $faker->name,
        "start_date" => $date,
        "end_date" => Carbon::parse($date)->addDays(12),
        "status" => $status[$random],
        "created_by" => 1,
    ];
});

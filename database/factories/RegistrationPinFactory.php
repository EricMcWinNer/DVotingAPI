<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\RegistrationPin;
use Faker\Generator as Faker;

$factory->define(RegistrationPin::class, function (Faker $faker) {
    return [
        //
        'content' => random_int(111111111, 999999999),
        'created_by' => 1

    ];
});

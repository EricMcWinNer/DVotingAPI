<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Candidate::class, function (Faker $faker) {
    $partyId = random_int(13,29);
    $party = \App\Party::find($partyId);
    return [
        "name" => $faker->name,
        "user_id" => random_int(19, 64),
        "party_id" => $partyId,
        "role" => "Vice-President",
        "party_name" => $party->name,
        "election_id" => 31,
        "candidate_picture" => "candidate-pictures/119.jpg"
    ];
});

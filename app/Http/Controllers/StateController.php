<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\State;
use App\LocalGovernment;
use League\Flysystem\Adapter\Local;

class StateController extends Controller
{
    //

    public function states()
    {
        $states = State::all();
        $states = $states->map(function ($state) {
            return [
                "state_id" => $state->state_id,
                "name" => $state->name
            ];
        });
        return response(
            [
                "status" => "success",
                "states" => $states
            ]
        );
    }

    public function lgas($id)
    {
        $lgas = LocalGovernment::where('state_id', $id)->get();
        $lgas = $lgas->map(function ($lga) {
            return [
                "lga_id" => $lga->id,
                "name" => $lga->name
            ];
        });
        return response([
            "status" => "success",
            "lgas" => $lgas
        ]);
    }
}

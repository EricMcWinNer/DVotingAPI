<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\State;
use App\LocalGovernment;
use League\Flysystem\Adapter\Local;

/**
 * Class StateController
 * @package App\Http\Controllers
 */
class StateController extends Controller
{
    //

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function states()
    {
        $states = State::all();
        $states = $states->map(function (State $state)
        {
            return [
                "state_id" => $state->state_id,
                "name"     => $state->name
            ];
        });
        return response([
            "status" => "success",
            "states" => $states
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function lgas($id)
    {
        $lgas = LocalGovernment::where('state_id', $id)->orderBy('name', 'asc')->get();
        $lgas = $lgas->map(function (LocalGovernment $lga)
        {
            return [
                "lga_id" => $lga->id,
                "name"   => $lga->name
            ];
        });
        return response([
            "status" => "success",
            "lgas"   => $lgas
        ]);
    }

    public function lga()
    {
        $lgas = LocalGovernment::orderBy('name', 'asc')->get();
        return response(["lgas" => $lgas]);
    }

    public function statesLga($id)
    {
        $states = State::all();
        $lgas = LocalGovernment::where('state_id', $id)->orderBy('name', 'asc')->get();
        $lgas = $lgas->map(function (LocalGovernment $lga)
        {
            return [
                "lga_id" => $lga->id,
                "name"   => $lga->name
            ];
        });
        return response([
            "states" => $states,
            "lgas"   => $lgas
        ]);
    }
}

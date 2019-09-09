<?php

namespace App\Http\Controllers;

use App\LocalGovernment;
use App\OfficerRegister;
use App\State;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class VoterController
 * @package App\Http\Controllers
 */
class VoterController extends Controller
{
    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index($perPage = 20)
    {
        $voters = User::with('lga.state')->orderBy('name', 'asc')->paginate($perPage);
        if (!isset($_GET["page"]))
        {
            $lgas = LocalGovernment::with('state')->get();
            $states = State::all();
            return response([
                "states" => $states,
                "lgas"   => $lgas,
                "voters" => $voters
            ]);
        }
        return response([
            "voters" => $voters,
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getVoterById($id)
    {
        $voter = User::with('lga.state')->where('id', $id)->first();
        $officer = OfficerRegister::with('officer.lga.state')->where('voter_id', $id)->first();
        return response(["voter"   => $voter,
                         "officer" => $officer
        ]);
    }

    /**
     * @param $needle
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function genericSearch($needle, $perPage = 20)
    {
        $voters = null;
        if (isset($_GET["filter_by"]))
        {
            if ($_GET["filter_by"] == "state")
            {
                $voters = User::with('lga.state')->where(function ($query) use ($needle)
                {
                    $query->where('email', 'like', '%' . $needle . '%')
                          ->orWhere('name', 'like', '%' . $needle . '%')
                          ->orWhere('address1', 'like', '%' . $needle . '%')
                          ->orWhere('address2', 'like', '%' . $needle . '%')
                          ->orWhere('marital_status', 'like', '%' . $needle . '%')
                          ->orWhere('occupation', 'like', '%' . $needle . '%')
                          ->orWhere('gender', 'like', '%' . $needle . '%')
                          ->orWhere('phone_number', 'like', '%' . $needle . '%');
                })->where('state_id', (int)$_GET["filter_value"])->orderBy('name', 'asc')
                              ->paginate($perPage);
            }
            else
            {
                $voters = User::with('lga.state')->where(function ($query) use ($needle)
                {
                    $query->where('email', 'like', '%' . $needle . '%')
                          ->orWhere('name', 'like', '%' . $needle . '%')
                          ->orWhere('address1', 'like', '%' . $needle . '%')
                          ->orWhere('address2', 'like', '%' . $needle . '%')
                          ->orWhere('marital_status', 'like', '%' . $needle . '%')
                          ->orWhere('occupation', 'like', '%' . $needle . '%')
                          ->orWhere('gender', 'like', '%' . $needle . '%')
                          ->orWhere('phone_number', 'like', '%' . $needle . '%');
                })->where('lga_id', (int)$_GET["filter_value"])->orderBy('name', 'asc')
                              ->paginate($perPage);
            }
        }
        else
        {
            $voters = User::with('lga.state')->where('email', 'like', '%' . $needle . '%')
                          ->orWhere('name', 'like', '%' . $needle . '%')
                          ->orWhere('address1', 'like', '%' . $needle . '%')
                          ->orWhere('address2', 'like', '%' . $needle . '%')
                          ->orWhere('marital_status', 'like', '%' . $needle . '%')
                          ->orWhere('occupation', 'like', '%' . $needle . '%')
                          ->orWhere('gender', 'like', '%' . $needle . '%')
                          ->orWhere('phone_number', 'like', '%' . $needle . '%')
                          ->orderBy('name', 'asc')->paginate($perPage);
        }
        return response(["voters" => $voters]);

    }

    /**
     * @param $state
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function filterByState($state, $perPage = 20)
    {
        $voters = User::with('lga.state')->where('state_id', $state)->paginate($perPage);
        return response(["voters" => $voters]);
    }

    /**
     * @param $lga
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function filterByLGA($lga, $perPage = 20)
    {
        $voters = User::with('lga.state')->where('lga_id', $lga)->paginate($perPage);
        return response(["voters" => $voters]);
    }


}

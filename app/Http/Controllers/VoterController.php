<?php

namespace App\Http\Controllers;

use App\LocalGovernment;
use App\State;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VoterController extends Controller
{
    private function returnVotersWithStates($voters)
    {
        $votersWithStates = $voters->map(function (User $voter)
        {
            $lga = $voter->lga_id;
            $lga = LocalGovernment::find($lga);
            $state = State::find($lga->state_id);
            $voter->state = $state->name;
            $voter->lga = $lga->name;
            $voter->age = [
                "dob_string" => Carbon::parse($voter->dob)
                                      ->format('jS M, Y'),
                "age"        => Carbon::parse($voter->dob)->age
            ];
            return $voter;
        });
        return response([
            "voters"        => $votersWithStates,
            "current_page"  => $voters->currentPage(),
            "last_page"     => $voters->lastPage(),
            "per_page"      => $voters->perPage(),
            "next_page_url" => $voters->nextPageUrl(),
            "total_results" => $voters->total()
        ]);
    }

    public function index($perPage = 20)
    {
        $voters = User::orderBy('name', 'asc')
                      ->paginate($perPage);
        $votersWithStates = $voters->map(function (User $voter)
        {
            $lga = $voter->lga_id;
            $lga = LocalGovernment::find($lga);
            $state = State::find($lga->state_id);
            $voter->age = [
                "dob_string" => Carbon::parse($voter->dob)
                                      ->format('jS M, Y'),
                "age"        => Carbon::parse($voter->dob)->age
            ];
            $voter->state = $state->name;
            $voter->lga = $lga->name;
            return $voter;
        });
        if (!isset($_GET["page"]))
        {
            $states = State::all();
            $lgas = LocalGovernment::all();
            $lgas = $lgas->map(function ($lga)
            {
                $lga->state = State::where('state_id', $lga->state_id)
                                   ->first()->name;
                return $lga;
            });
            return response([
                "voters"        => $votersWithStates,
                "current_page"  => $voters->currentPage(),
                "last_page"     => $voters->lastPage(),
                "per_page"      => $voters->perPage(),
                "next_page_url" => $voters->nextPageUrl(),
                "total_results" => $voters->total(),
                "states"        => $states,
                "lgas"          => $lgas
            ]);
        }
        return response([
            "voters"        => $votersWithStates,
            "current_page"  => $voters->currentPage(),
            "last_page"     => $voters->lastPage(),
            "per_page"      => $voters->perPage(),
            "next_page_url" => $voters->nextPageUrl(),
            "total_results" => $voters->total(),
        ]);
    }

    public function getVoterById($id)
    {
        $voter = User::find($id);
        $lga = $voter->lga_id;
        $lga = LocalGovernment::find($lga);
        $state = State::find($lga->state_id);
        $voter->state = $state->name;
        $voter->lga = $lga->name;
        $voter->age = [
            "dob_string" => Carbon::parse($voter->dob)
                                  ->format('jS M, Y'),
            "age"        => Carbon::parse($voter->dob)->age
        ];
        $voter->date_created = Carbon::parse($voter->created_at)
                                     ->format('jS M, Y');
        return response(["voter" => $voter]);
    }

    public function genericSearch($needle, $perPage = 20)
    {
        $voters = null;
        if (isset($_GET["filter_by"]))
        {
            if ($_GET["filter_by"] == "state")
            {
                $voters = User::where(function ($query) use ($needle)
                {
                    $query->where('email', 'like',
                        '%' . $needle . '%')
                          ->orWhere('name', 'like',
                              '%' . $needle . '%')
                          ->orWhere('address1', 'like',
                              '%' . $needle . '%')
                          ->orWhere('address2', 'like',
                              '%' . $needle . '%')
                          ->orWhere('marital_status', 'like',
                              '%' . $needle . '%')
                          ->orWhere('occupation', 'like',
                              '%' . $needle . '%')
                          ->orWhere('gender', 'like',
                              '%' . $needle . '%')
                          ->orWhere('phone_number', 'like',
                              '%' . $needle . '%');
                })
                              ->where('state_id',
                                  (int)$_GET["filter_value"])
                              ->orderBy('name', 'asc')
                              ->paginate($perPage);
            }
            else
            {
                $voters = User::where(function ($query) use ($needle)
                {
                    $query->where('email', 'like',
                        '%' . $needle . '%')
                          ->orWhere('name', 'like',
                              '%' . $needle . '%')
                          ->orWhere('address1', 'like',
                              '%' . $needle . '%')
                          ->orWhere('address2', 'like',
                              '%' . $needle . '%')
                          ->orWhere('marital_status', 'like',
                              '%' . $needle . '%')
                          ->orWhere('occupation', 'like',
                              '%' . $needle . '%')
                          ->orWhere('gender', 'like',
                              '%' . $needle . '%')
                          ->orWhere('phone_number', 'like',
                              '%' . $needle . '%');
                })
                              ->where('lga_id',
                                  (int)$_GET["filter_value"])
                              ->orderBy('name', 'asc')
                              ->paginate($perPage);
            }
        }
        else
        {
            $voters =
                User::where('email', 'like', '%' . $needle . '%')
                    ->orWhere('name', 'like', '%' . $needle . '%')
                    ->orWhere('address1', 'like', '%' . $needle . '%')
                    ->orWhere('address2', 'like', '%' . $needle . '%')
                    ->orWhere('marital_status', 'like',
                        '%' . $needle . '%')
                    ->orWhere('occupation', 'like',
                        '%' . $needle . '%')
                    ->orWhere('gender', 'like', '%' . $needle . '%')
                    ->orWhere('phone_number', 'like',
                        '%' . $needle . '%')
                    ->orderBy('name', 'asc')
                    ->paginate($perPage);
        }
        return $this->returnVotersWithStates($voters);

    }

    public function filterByState($state, $perPage = 20)
    {
        $voters = User::where('state_id', $state)
                      ->paginate($perPage);
        return $this->returnVotersWithStates($voters);
    }

    public function filterByLGA($lga, $perPage = 20)
    {
        $voters = User::where('lga_id', $lga)
                      ->paginate($perPage);
        return $this->returnVotersWithStates($voters);
    }


}

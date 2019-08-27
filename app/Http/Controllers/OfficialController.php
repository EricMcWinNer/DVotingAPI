<?php

namespace App\Http\Controllers;

use App\Event\OfficialDeleted;
use App\Events\OfficialCreated;
use App\LocalGovernment;
use App\State;
use App\User;
use App\Utils\UserHelper;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    public function getEligibleOfficials($perPage = 20)
    {
        $users = User::with('lga.state')->whereJsonDoesntContain("roles", "official")
                     ->whereJsonDoesntContain("roles", "candidate")->whereJsonDoesntContain("roles", "officer")
                     ->orderBy('name', 'asc')->paginate($perPage);
        if (!isset($_GET["page"]))
        {
            $lga = LocalGovernment::with('state')->orderBy('name', 'asc')->get();
            $state = State::orderBy('name', 'asc')->get();
            return response([
                "users"  => $users,
                "lgas"   => $lga,
                "states" => $state
            ]);
        }
        return response(["users" => $users]);
    }

    public function searchEligibleOfficials($needle, $perPage = 20)
    {
        $officials = null;
        if (isset($_GET["filter_by"]))
        {
            switch ($_GET["filter_by"])
            {
                case "state":
                    $state = (int)$_GET["filter_value"];
                    $officials = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")->orWhere('phone_number', 'like', "%{$needle}%");

                    })->whereJsonDoesntContain("roles", "official")->whereJsonDoesntContain("roles", "candidate")
                                     ->whereJsonDoesntContain("roles", "officer")->orderBy('name', 'asc')
                                     ->where("state_id", $state)->paginate($perPage);
                    break;
                default:
                    $lga = (int)$_GET["filter_value"];
                    $officials = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")->orWhere('phone_number', 'like', "%{$needle}%");

                    })->whereJsonDoesntContain("roles", "official")->whereJsonDoesntContain("roles", "candidate")
                                     ->whereJsonDoesntContain("roles", "officer")->orderBy('name', 'asc')
                                     ->where("state_id", $lga)->paginate($perPage);
                    break;
            }
        }
        else
        {
            $officials = User::with('lga.state')->where(function ($query) use ($needle)
            {
                $query->where('name', 'like', "%{$needle}%")->orWhere('phone_number', 'like', "%{$needle}%");
            })->whereJsonDoesntContain("roles", "official")->whereJsonDoesntContain("roles", "candidate")
                             ->whereJsonDoesntContain("roles", "officer")->orderBy('name', 'asc')->paginate($perPage);
        }
        return response(["users" => $officials]);
    }

    public function filterEligibleOfficialsByState($id, $perPage = 20)
    {
        $officials = User::with('lga.state')->whereJsonDoesntContain("roles", "official")
                         ->whereJsonDoesntContain("roles", "candidate")->whereJsonDoesntContain("roles", "officer")
                         ->where("state_id", $id)->orderBy('name', 'asc')->paginate($perPage);
        return response(["users" => $officials]);
    }

    public function filterEligibleOfficialsByLGA($id, $perPage = 20)
    {
        $officials = User::with('lga.state')->whereJsonDoesntContain("roles", "candidate")
                         ->whereJsonDoesntContain("roles", "officer")->where("state_id", $id)->orderBy('name', 'asc')
                         ->paginate($perPage);
        return response(["users" => $officials]);
    }

    public function create($id)
    {
        $user = User::find($id);
        if (is_null($user)) return response(["completed" => false]);
        $user = UserHelper::makeOfficial($user);
        $user->save();
        event(new OfficialCreated($user));
        return response(["completed" => true]);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (is_null($user)) return response(["completed" => false]);
        $user = UserHelper::makeVoter($user);
        $user->save();
        event(new OfficialDeleted($user));
        return response(["completed" => true]);
    }

    public function index($perPage = 20)
    {
        $officials =
            User::with('lga.state')->whereJsonContains('roles', 'official')->orderBy('name', 'asc')->paginate($perPage);
        if (!isset($_GET["page"]))
        {
            $lga = LocalGovernment::with('state')->orderBy('name', 'asc')->get();
            $state = State::orderBy('name', 'asc')->get();
            return response([
                "officials" => $officials,
                "lgas"      => $lga,
                "states"    => $state
            ]);
        }
        return response(["officials" => $officials]);
    }

    public function search($needle, $perPage = 20)
    {
        $officials = null;
        if (isset($_GET["filter_by"]))
        {
            switch ($_GET["filter_by"])
            {
                case "state":
                    $state = (int)$_GET["filter_value"];
                    $officials = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")->orWhere('phone_number', 'like', "%{$needle}%");

                    })->whereJsonContains("roles", "official")->where("state_id", $state)->orderBy('name', 'asc')
                                     ->paginate($perPage);
                    break;
                default:
                    $lga = (int)$_GET["filter_value"];
                    $officials = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")->orWhere('phone_number', 'like', "%{$needle}%");

                    })->whereJsonContains("roles", "official")->where("state_id", $lga)->orderBy('name', 'asc')
                                     ->paginate($perPage);
                    break;
            }
        }
        else
        {
            $officials = User::with('lga.state')->where(function ($query) use ($needle)
            {
                $query->where('name', 'like', "%{$needle}%")->orWhere('phone_number', 'like', "%{$needle}%");
            })->whereJsonContains('roles', 'official')->orderBy('name', 'asc')->paginate($perPage);
        }
        return response(["officials" => $officials]);
    }

    public function filterOfficialsByState($id, $perPage = 20)
    {
        $officials = User::with('lga.state')->whereJsonContains("roles", "official")->where("state_id", $id)
                         ->orderBy('name', 'asc')->paginate($perPage);
        return response(["officials" => $officials]);
    }


    public function filterOfficialsByLGA($id, $perPage = 20)
    {
        $officials = User::with('lga.state')->whereJsonContains("roles", "official")->where("lga_id", $id)
                         ->orderBy('name', 'asc')->paginate($perPage);
        return response(["officials" => $officials]);
    }
}
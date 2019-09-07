<?php

namespace App\Http\Controllers;

use App\LocalGovernment;
use App\OfficerRegister;
use App\State;
use App\User;
use App\Utils\UserHelper;
use Illuminate\Http\Request;

/**
 * Class OfficerController
 * @package App\Http\Controllers
 */
class OfficerController extends Controller
{
    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index($perPage = 20)
    {
        $officers =
            User::with('lga.state')->whereJsonContains("roles", "officer")->orderBy('name', 'asc')
                ->paginate($perPage);
        if (!isset($_GET["page"]))
        {
            $lga = LocalGovernment::with('state')->orderBy('name', 'asc')->get();
            $state = State::orderBy('name', 'asc')->get();
            return response([
                "officers" => $officers,
                "lgas"     => $lga,
                "states"   => $state
            ]);
        }
        return response(["officers" => $officers]);
    }

    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getEligibleOfficers($perPage = 20)
    {
        $users = User::with('lga.state')->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')
                     ->whereJsonDoesntContain('roles', 'officer')->orderBy('name', 'asc')
                     ->paginate($perPage);
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

    /**
     * @param $needle
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function searchEligibleOfficers($needle, $perPage = 20)
    {
        $users = null;
        if (isset($_GET['filter_by']) && isset($_GET['filter_value']))
        {
            switch ($_GET['filter_by'])
            {
                case "state":
                    $state = (int)$_GET['filter_value'];
                    $users = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")
                              ->orWhere('phone_number', 'like', "%{$needle}%");
                    })->whereJsonDoesntContain('roles', 'candidate')
                                 ->whereJsonDoesntContain('roles', 'official')
                                 ->whereJsonDoesntContain('roles', 'officer')
                                 ->where('state_id', $state)->orderBy('name', 'asc')
                                 ->paginate($perPage);
                    break;
                default:
                    $lga = (int)$_GET['filter_value'];
                    $users = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")
                              ->orWhere('phone_number', 'like', "%{$needle}%");
                    })->whereJsonDoesntContain('roles', 'candidate')
                                 ->whereJsonDoesntContain('roles', 'official')
                                 ->whereJsonDoesntContain('roles', 'officer')->where('lga_id', $lga)
                                 ->orderBy('name', 'asc')->paginate($perPage);
                    break;
            }
        }
        else
        {
            $users = User::with('lga.state')->where(function ($query) use ($needle)
            {
                $query->where('name', 'like', "%{$needle}%")
                      ->orWhere('phone_number', 'like', "%{$needle}%");
            })->whereJsonDoesntContain('roles', 'candidate')
                         ->whereJsonDoesntContain('roles', 'official')
                         ->whereJsonDoesntContain('roles', 'officer')->orderBy('name', 'asc')
                         ->paginate($perPage);
        }
        return response(["users" => $users]);
    }

    /**
     * @param $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function filterEligibleOfficersByState($id, $perPage = 20)
    {
        $users = User::with('lga.state')->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')
                     ->whereJsonDoesntContain('roles', 'officer')->where('state_id', $id)
                     ->paginate($perPage);
        return response(["users" => $users]);
    }

    /**
     * @param $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function filterEligibleOfficersByLGA($id, $perPage = 20)
    {
        $users = User::with('lga.state')->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')
                     ->whereJsonDoesntContain('roles', 'officer')->where('lga_id', $id)
                     ->paginate($perPage);
        return response(["users" => $users]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function confirmOfficerCreation($id)
    {
        $user = User::find($id);
        if (is_null($user) || !UserHelper::isOnlyVoter($user)) return response(["user" => null]);
        return response(["user" => $user]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create($id)
    {
        $user = User::find($id);
        if (is_null($user) ||
            !UserHelper::isOnlyVoter($user)) return response(["completed" => false]);
        $user = UserHelper::makeOfficer($user);
        $user->save();
        return response(["completed" => true]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($id)
    {
        $officer = User::find($id);
        if (is_null($officer) ||
            !UserHelper::isOfficer($officer)) return response(["completed" => false]);
        $user = UserHelper::makeVoter($officer);
        $user->save();
        /*event(new OfficerDeleted($user));*/
        return response(["completed" => true]);
    }

    /**
     * @param $needle
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function search($needle, $perPage = 20)
    {
        $officers = null;
        if (isset($_GET['filter_by']) && isset($_GET['filter_value']))
        {
            switch ($_GET['filter_by'])
            {
                case "state":
                    $state = (int)$_GET['filter_value'];
                    $officers = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")
                              ->orWhere('phone_number', 'like', "%{$needle}%");
                    })->whereJsonContains('roles', 'officer')->where('state_id', $state)
                                    ->orderBy('name', 'asc')->paginate($perPage);
                    break;
                default:
                    $lga = (int)$_GET['filter_value'];
                    $officers = User::with('lga.state')->where(function ($query) use ($needle)
                    {
                        $query->where('name', 'like', "%{$needle}%")
                              ->orWhere('phone_number', 'like', "%{$needle}%");
                    })->whereJsonContains('roles', 'officer')->where('lga_id', $lga)
                                    ->orderBy('name', 'asc')->paginate($perPage);
                    break;
            }
        }
        else
        {
            $officers = User::with('lga.state')->where(function ($query) use ($needle)
            {
                $query->where('name', 'like', "%{$needle}%")
                      ->orWhere('phone_number', 'like', "%{$needle}%");
            })->whereJsonContains('roles', 'officer')->orderBy('name', 'asc')->paginate($perPage);
        }
        return response(["officers" => $officers]);
    }

    /**
     * @param $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function filterOfficersByState($id, $perPage = 20)
    {
        $officers =
            User::with('lga.state')->whereJsonContains('roles', 'officer')->where('state_id', $id)
                ->paginate($perPage);
        return response(["officers" => $officers]);
    }

    /**
     * @param $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function filterOfficersByLGA($id, $perPage = 20)
    {
        $officers =
            User::with('lga.state')->whereJsonContains('roles', 'officer')->where('lga_id', $id)
                ->paginate($perPage);
        return response(["officers" => $officers]);
    }

    public function read($id)
    {
        $officer = User::with('lga.state')->whereJsonContains('roles', 'officer')->where("id", $id)
                       ->first();
        return response(["officer" => $officer]);
    }

    public function getVotersRegisteredByOfficer($id, $perPage = 20)
    {
        $officer = User::find($id);
        if (!UserHelper::isOfficer($officer)) return response(["officer" => null]);
        $voters = OfficerRegister::with(['voter.lga.state'])->where('officer_id', $id)
                                 ->orderBy('created_at', 'desc')->paginate($perPage);
        return response([
            "officer" => $officer,
            "voters"  => $voters
        ]);
    }
}

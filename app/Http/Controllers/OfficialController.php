<?php

namespace App\Http\Controllers;

use App\User;
use App\Utils\UserHelper;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    public function getEligibleOfficials($perPage = 20)
    {
        $users = User::whereJsonDoesntContain("roles", "official")->paginate($perPage);
        return response(["users" => $users]);
    }

    public function create($id)
    {
        $user = User::find($id);
        if (is_null($user)) return response(["completed" => false]);
        $user = UserHelper::makeOfficial($user);
        $user->save();
        return response(["completed" => true]);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (is_null($user)) return response(["completed" => false]);
        $user = UserHelper::makeVoter($user);
        $user->save();
        return response(["completed" => true]);
    }
}

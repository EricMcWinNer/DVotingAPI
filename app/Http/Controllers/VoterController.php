<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class VoterController extends Controller
{
    public function index($id = 20)
    {
        $voters = User::whereJsonContains("roles", "voter")
            ->paginate($id);
        return response(["voters" => $voters]);
    }

    public function getVoterById($id)
    {
        $voter = User::find($id);
        return response(["voter" => $voter]);
    }

    public function searchVoters(Request $request)
    {
        if (!is_null($request->email)) {
            $email = $request->email;
            $voters = User::where('email', 'like', '%' . $email . '%')->get();
            return response(["voters", $voters]);
        } else if (!is_null($request->name)) {
            $name = $request->name;
            $voters = User::where('name', 'like', '%' . $name . '%')->get();
            return response(["voters", $voters]);
        } else {
            return response(["err" => "Required fields not found"]);
        }

    }


}

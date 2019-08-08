<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function getUser(Request $request)
    {
        return response([
            "isSessionValid" => "true",
            "user" => $request->user()
        ]);
    }
}

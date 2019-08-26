<?php

namespace App\Http\Controllers;

use App\Party;
use App\User;
use App\Utils\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function getUser(Request $request)
    {
        return response([
            "isSessionValid" => "true",
            "user" => $request->user(),
        ]);
    }

    public function initializeHomePage(Request $request)
    {
        $electionInfo = app(ElectionController::class)->getCurrentElectionMinimalInfo();
        $voterCount = User::count();
        $partiesCount = Party::count();
        $voterCreatedLast = Utility::dateStringParser(User::latest('created_at')->first()->created_at["created_at_default"]);
        $partyCreatedLast = Utility::dateStringParser(Party::latest('created_at')->first()->created_at);
        return response([
            "isSessionValid" => "true",
            "election" => $electionInfo,
            "voters" => [
                "count" => Utility::shortTextifyNumbers($voterCount),
                "lastCreated" => $voterCreatedLast
            ],
            "parties" => [
                "count" => $partiesCount,
                "lastCreated" => $partyCreatedLast
            ]
        ]);
    }
}

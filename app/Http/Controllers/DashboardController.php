<?php

namespace App\Http\Controllers;

use App\Party;
use App\User;
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

    private function dateStringParser($date)
    {
        $date = new Carbon($date);
        if ($date->isSameMinute())
            return "just now";
        else if ($date->isSameHour())
            return Carbon::now()->diffInMinutes($date) . " mins ago";
        else if ($date->isSameDay())
            return $date->format('H:i');
        else if ($date->isYesterday())
            return "yesterday, " . $date->format('H:i');
        else if ($date->isSameYear())
            return $date->format("jS, M");
    }

    public function initializeHomePage(Request $request)
    {
        $electionInfo = app(ElectionController::class)->getCurrentElectionMinimalInfo();
        $voterCount = User::count();
        $partiesCount = Party::count();
        $voterCreatedLast = $this->dateStringParser(User::latest('created_at')->first()->created_at);
        $partyCreatedLast = $this->dateStringParser(Party::latest('created_at')->first()->created_at);
        return response([
            "isSessionValid" => "true",
            "election" => $electionInfo,
            "voters" => [
                "count" => $voterCount,
                "lastCreated" => $voterCreatedLast
            ],
            "parties" => [
                "count" => $partiesCount,
                "lastCreated" => $partyCreatedLast
            ]
        ]);
    }
}

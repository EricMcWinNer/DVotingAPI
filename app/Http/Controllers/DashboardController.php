<?php

namespace App\Http\Controllers;

use App\Candidate;
use App\OfficerRegister;
use App\Party;
use App\User;
use App\Utils\UserHelper;
use App\Utils\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        return response([
            "isSessionValid" => "true",
            "user"           => $request->user(),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function initializeHomePage(Request $request)
    {
        $user = $request->user();
        $officerInfo = null;
        $officialInfo = null;
        $electionInfo = app(ElectionController::class)->getCurrentElectionMinimalInfo();
        $voterCount = User::count();
        $partiesCount = Party::count();
        $candidatesCount = Candidate::count();
        $pollingOfficersCount = User::whereJsonContains('roles', 'officer')->count();
        $voterCreatedLast = Utility::dateStringParser(User::latest('created_at')
                                                          ->first()->created_at["created_at_default"]);
        $partyCreatedLast =
            Utility::dateStringParser(Party::latest('created_at')->first()->created_at);
        $candidateCreatedLast = $candidatesCount > 0 ?
            Utility::dateStringParser(Candidate::latest('created_at')->first()->created_at) : "N/A";
        $officerCreatedLast = $pollingOfficersCount > 0 ?
            Utility::dateStringParser(User::whereJsonContains('roles', 'officer')
                                          ->latest('created_at')
                                          ->first()->created_at["created_at_default"]) : "N/A";
        if (UserHelper::isOfficer($user))
        {
            $totalVotersRegistered = OfficerRegister::where('officer_id', $user->id)->count();
            $totalVotersRegisteredToday = OfficerRegister::where('officer_id', $user->id)
                                                         ->whereDate('created_at', Carbon::today("UTC")
                                                                                         ->format('Y-m-d'))
                                                         ->count();
            $officerInfo = [
                "total_voters_registered"       => $totalVotersRegistered,
                "total_voters_registered_today" => $totalVotersRegisteredToday,
            ];
        }

        return response([
            "isSessionValid" => "true",
            "election"       => $electionInfo,
            "voters"         => [
                "count"       => Utility::shortTextifyNumbers($voterCount),
                "lastCreated" => $voterCreatedLast,
            ],
            "parties"        => [
                "count"       => $partiesCount,
                "lastCreated" => $partyCreatedLast,
            ],
            "candidates"     => [
                "count"       => $candidatesCount,
                "lastCreated" => $candidateCreatedLast,
            ],
            "officers"       => [
                "count"       => $pollingOfficersCount,
                "lastCreated" => $officerCreatedLast,
            ],
            "officer_info"   => $officerInfo
        ]);
    }
}

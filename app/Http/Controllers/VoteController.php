<?php

namespace App\Http\Controllers;

use App\Party;
use App\User;
use App\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VoteController extends Controller
{
    private function voted(User $user): bool
    {
        $election = app(ElectionController::class)->getCurrentElection();
        return Vote::where([['user_id', $user->id], ['election_id', $election->id]])
                   ->count() == 1;
    }

    public function initializeVotesPage(Request $request)
    {
        $user = $request->user();
        $election = app(ElectionController::class)->getCurrentElection();
        if (is_null($election)) return response(["parties" => null]);
        else if ($election->status !== "ongoing") return response(["parties" => null]);
        else {
            $parties = Party::with(['candidates' => function ($query) use ($election) {
                $query->where('election_id', $election->id)
                      ->orderBy('role', 'asc');
            }])
                            ->orderBy('name', 'asc')
                            ->get();
            $parties = $parties->filter(function ($party) {
                return count($party->candidates) > 1;
            })
                               ->values();
            $voted = Vote::where([['user_id', $user->id], ['election_id', $election->id]])
                         ->count();
            return response(["parties" => $parties, "voted" => $voted == 1]);
        }
    }

    public function checkIfVoted(Request $request)
    {
        $voted = $this->voted($request->user());
        return response(["voted" => $voted]);
    }

    public function forward(Request $request, $id)
    {
        $voted = $this->voted($request->user());
        $party = Party::find($id);
        return response(["voted" => $voted, "party" => $party]);
    }

    public function vote(Request $request, $partyId)
    {
        $user = $request->user();
        $election = app(ElectionController::class)->getCurrentElection();
        $hashedPassword = DB::table('users')
                            ->select('password')
                            ->where('id', $user->id)
                            ->first()->password;
        if ($this->voted($user)) return response(["voted" => true]);
        else if (!Hash::check($request->password, $hashedPassword)) return response(["password" => "wrong"]);
        //INSERT FINGERPRINT VERIFICATION CODE
        else {
            $vote = new Vote;
            $vote->user_id = $user->id;
            $vote->party_id = $partyId;
            $vote->election_id = $election->id;
            $vote->lga_id = $user->lga_id;
            $vote->save();
            return response(["completed" => true]);
        }

    }
}

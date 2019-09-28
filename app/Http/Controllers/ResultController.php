<?php

namespace App\Http\Controllers;


use App\LocalGovernment;
use App\Party;
use App\State;
use App\Utils\Utility;
use App\Vote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function getResults()
    {
        $election = app(ElectionController::class)->getCurrentElection();
        $totalVotes = Vote::count();
        $lastVote = Vote::orderBy('created_at', 'desc')->first();
        #Most voted party calculation
        $mostVotedRs = DB::table('votes')->select(DB::raw('party_id, COUNT(*) as total'))
                         ->groupBy('party_id')
                         ->orderByRaw('COUNT(*) DESC')->first();
        $mostVoted = Party::find($mostVotedRs->party_id);
        $mostVoted->percentage = round((($mostVotedRs->total / $totalVotes) * 100), PHP_ROUND_HALF_UP) . "%";
        $mostVoted->textifiedTotal = Utility::shortTextifyNumbers($mostVotedRs->total);

        #Least voted party calculation
        $leastVotedRs = DB::table('votes')->select(DB::raw('party_id, COUNT(*) as total'))
                          ->groupBy('party_id')
                          ->orderByRaw('COUNT(*) ASC')->first();
        $leastVoted = Party::find($leastVotedRs->party_id);
        $leastVoted->percentage = round((($leastVotedRs->total / $totalVotes) * 100), PHP_ROUND_HALF_UP) . "%";
        $leastVoted->textifiedTotal = Utility::shortTextifyNumbers($leastVotedRs->total);

        #Calculate parties voted for
        $numberOfParties = DB::table('votes')->distinct()->count('party_id');
        $totalParties = DB::table('candidates')->where('election_id', $election->id)
                          ->distinct()->count('party_id');

        #Get states and LGAS
        $lgas = LocalGovernment::with('state')->get();
        $states = State::all();

        return response([
            "total_votes"       => $totalVotes,
            "last_voted_at"     => Carbon::parse($lastVote->created_at)->format('Y-m-d H:i:s'),
            "most_voted_party"  => $mostVoted,
            "least_voted_party" => $leastVoted,
            "number_of_parties" => $numberOfParties,
            "total_parties"     => $totalParties,
            "states"            => $states,
            "lgas"              => $lgas,
        ]);
    }

    public function getPieChartData($number)
    {
        $totalVotes = Vote::count();
        if ($number <= 4) {
            $parties = Party::select('acronym')->withCount('votes')->orderBy('name', 'asc')->get()
                            ->map(function ($party) use ($totalVotes) {
                                $pseudoParty = new \stdClass();
                                $pseudoParty->percent = round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP);
                                $pseudoParty->x = $party->acronym . " \n (" . $pseudoParty->percent . "%)";
                                $pseudoParty->y = $party->votes_count;
                                return $pseudoParty;
                            })->filter(function ($vote) {
                    return $vote->y !== 0;
                })->values();
            return response(["parties" => $parties]);
        } else {
            $parties = Party::select('acronym')->withCount('votes')->orderByRaw('votes_count DESC')
                            ->orderBy('name', 'asc')->limit(3)->get()
                            ->map(function ($party) use ($totalVotes) {
                                $pseudoParty = new \stdClass();
                                $pseudoParty->percent = round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP);
                                $pseudoParty->x = $party->acronym . " \n (" . $pseudoParty->percent . "%)";
                                $pseudoParty->y = $party->votes_count;
                                return $pseudoParty;
                            });
            $others = Party::select('acronym')->withCount('votes')->orderByRaw('votes_count DESC')
                           ->orderBy('name', 'asc')->skip(3)->take(999999)->get()
                           ->reduce(function ($carry, $item) {
                               return $carry + $item->votes_count;
                           });

            $pseudoParty = new \stdClass();
            $pseudoParty->percent = round((($others / $totalVotes) * 100), PHP_ROUND_HALF_UP);
            $pseudoParty->x = "Others \n (" . $pseudoParty->percent . "%)";
            $pseudoParty->y = $others;
            $parties->push($pseudoParty);
            return response(["parties" => $parties]);
        }
    }

    public function getPartiesVotes()
    {
        $totalVotes = Vote::count();
        $parties = Party::withCount('candidates')->withCount('votes')
                        ->orderByRaw('votes_count DESC')->orderBy('name', 'asc')
                        ->get()->filter(function ($party) {
                return count($party->candidates_count) !== 0;
            })->filter(function ($party) {
                return count($party->candidates) !== 0;
            })
                        ->values()->map(function ($party) use ($totalVotes) {
                $party->percentage_of_votes = round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP);
                return $party;
            });
        return response(["parties" => $parties]);
    }

    public function getVotesByState($stateId)
    {
        $totalVotes = Vote::where('state_id', $stateId)->count();
        $state = State::where('state_id', $stateId)->first();
        $lgas = LocalGovernment::with('state')->where('state_id', $stateId)
                               ->orderBy('name', 'asc')->get();
        $parties = Party::with(['votes' =>
                                    function ($query) use ($stateId) {
                                        $query->where('state_id', $stateId);
                                    }])->withCount('candidates')
                        ->orderBy('name', 'asc')
                        ->get()->filter(function ($party) {
                return $party->candidates_count !== 0;
            })->values()->map(function ($party) use ($totalVotes) {
                $party->votes_count = Utility::shortTextifyNumbers(count($party->votes));
                unset($party->votes);
                $party->percentage_of_votes = $totalVotes !== 0
                    ? round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP) : 0;
                return $party;
            });
        return response(["parties"      => $parties,
                         "lgas"         => $lgas,
                         "state_object" => $state,
                         "lga_object"   => null,
                         "total_votes"  => $totalVotes
        ]);
    }

    public function getVotesByLGA($lgaId)
    {
        $totalVotes = Vote::where('lga_id', $lgaId)->count();
        $lga = LocalGovernment::find($lgaId);
        $parties = Party::with(['votes' =>
                                    function ($query) use ($lgaId) {
                                        $query->where('lga_id', $lgaId);
                                    }])->withCount('candidates')
                        ->orderBy('name', 'asc')
                        ->get()->filter(function ($party) {
                return $party->candidates_count !== 0;
            })->values()->map(function ($party) use ($totalVotes) {
                $party->votes_count = count($party->votes);
                unset($party->votes);
                $party->percentage_of_votes = $totalVotes !== 0
                    ? round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP) : 0;
                return $party;
            });
        return response(["parties"      => $parties,
                         "lga_object"   => $lga,
                         "state_object" => null,
                         "total_votes"  => $totalVotes
        ]);
    }

}

<?php

namespace App\Http\Controllers;


use App\LocalGovernment;
use App\Party;
use App\State;
use App\Utils\Utility;
use App\Vote;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Couchbase\ClassicAuthenticator;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function getResults()
    {
        $election = app(ElectionController::class)->getCurrentElection();
        $totalVotes = Vote::count();
        $now = Carbon::now();
        if(is_null($election)) return response(["election" => null]);
        $startDate = Carbon::parse($election->start_date);
        $endDate = Carbon::parse($election->end_date);
        if ($totalVotes < 1) return response(["no_results" => true]);
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

        #Duration and Time left
        $duration = $startDate->diffForHumans($endDate, CarbonInterface::DIFF_ABSOLUTE);
        $timeLeft = $election->status === "ongoing" && $now->lessThan($endDate)
            ? "About " . $endDate->diffForHumans($now, CarbonInterface::DIFF_ABSOLUTE) . " remaining"
            : "Election is complete";

        return response([
            "total_votes"       => $totalVotes,
            "last_voted_at"     => Carbon::parse($lastVote->created_at)->format('Y-m-d H:i:s'),
            "most_voted_party"  => $mostVoted,
            "least_voted_party" => $leastVoted,
            "number_of_parties" => $numberOfParties,
            "total_parties"     => $totalParties,
            "states"            => $states,
            "lgas"              => $lgas,
            "duration"          => $duration,
            "time_left"         => $timeLeft
        ]);
    }

    public function getPieChartData($number)
    {
        $election = app(ElectionController::class)->getCurrentElection();
        if(is_null($election)) return response(["election" => null]);
        $totalVotes = Vote::count();
        if ($totalVotes < 1) return response(["no_results" => true]);
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
                            })->filter(function ($party) {
                    return $party->y !== 0;
                })->values();
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
        $election = app(ElectionController::class)->getCurrentElection();
        if(is_null($election)) return response(["election" => null]);
        $totalVotes = Vote::count();
        if ($totalVotes < 1) return response(["no_results" => true]);
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
        return response(["parties"     => $parties,
                         "table_total" => $totalVotes]);
    }

    public function getVotesByState($stateId)
    {
        $election = app(ElectionController::class)->getCurrentElection();
        if(is_null($election)) return response(["election" => null]);
        $countryVotes = Vote::count();
        $totalVotes = Vote::where('state_id', $stateId)->count();
        $percentage = round((($totalVotes / $countryVotes) * 100), 1, PHP_ROUND_HALF_UP);
        $state = State::where('state_id', $stateId)->first();
        $state->percentage = $percentage;
        $lgas = LocalGovernment::with('state')->where('state_id', $stateId)
                               ->orderBy('name', 'asc')->get();
        $parties = Party::with(['votes' =>
                                    function ($query) use ($stateId) {
                                        $query->where('state_id', $stateId);
                                    }])->withCount('candidates')
                        ->orderBy('name', 'asc')
                        ->get()->filter(function ($party) {
                return $party->candidates_count !== 0;
            })->values()->map(function ($party) use ($totalVotes, $countryVotes) {
                $party->votes_count = Utility::shortTextifyNumbers(count($party->votes));
                unset($party->votes);
                $party->percentage_of_votes = $totalVotes !== 0
                    ? round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP) : 0;
                $party->percentage_of_votes_in_country = round((($party->votes_count / $countryVotes) * 100), PHP_ROUND_HALF_UP);
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
        $election = app(ElectionController::class)->getCurrentElection();
        if(is_null($election)) return response(["election" => null]);
        $lga = LocalGovernment::with('state')->find($lgaId);
        $totalStateVotes = Vote::where('state_id', $lga->state_id)->count();
        $totalCountryVotes = Vote::count();
        $totalVotes = Vote::where('lga_id', $lgaId)->count();
        $lga->percentInState = $totalStateVotes !== 0
            ? round((($totalVotes / $totalStateVotes) * 100), 1, PHP_ROUND_HALF_UP) : 0;
        $lga->percentInCountry = round((($totalVotes / $totalCountryVotes) * 100), 1, PHP_ROUND_HALF_UP);
        $parties = Party::with(['votes' =>
                                    function ($query) use ($lgaId) {
                                        $query->where('lga_id', $lgaId);
                                    }])->withCount('candidates')
                        ->orderBy('name', 'asc')
                        ->get()->filter(function ($party) {
                return $party->candidates_count !== 0;
            })->values()->map(function ($party) use ($totalVotes, $totalCountryVotes, $totalStateVotes) {
                $party->votes_count = count($party->votes);
                unset($party->votes);
                $party->percentage_of_votes = $totalVotes !== 0
                    ? round((($party->votes_count / $totalVotes) * 100), PHP_ROUND_HALF_UP) : 0;
                $party->percentage_of_votes_in_state = round((($party->votes_count / $totalStateVotes) * 100), PHP_ROUND_HALF_UP);
                $party->percentage_of_votes_in_country = round((($party->votes_count / $totalCountryVotes) * 100), PHP_ROUND_HALF_UP);
                return $party;
            });
        return response(["parties"      => $parties,
                         "lga_object"   => $lga,
                         "state_object" => null,
                         "total_votes"  => $totalVotes
        ]);
    }

    public function getAreaData()
    {
        $election = app(ElectionController::class)->getCurrentElection();
        if(is_null($election)) return response(["election" => null]);
        $now = Carbon::now();
        $electionStartDay = Carbon::parse($election->start_date);
        $electionEndDay = Carbon::parse($election->end_date);
        $difference = null;
        if ($now->greaterThan($electionEndDay)) {
            $differenceInDays = Carbon::parse($electionEndDay->toDateString())
                                      ->diffInDays(Carbon::parse($electionStartDay->toDateString()));
        } else {
            $differenceInDays = $now->diffInDays(Carbon::parse($electionStartDay->toDateString()));
        }
        $differenceInDays = $differenceInDays > 7 ? 7 : $differenceInDays;
        $data = [];
        if ($differenceInDays <= 1)
            if ($now->format('j') !== $electionStartDay->format('j')) {
                for ($i = 0; $i < 2; $i++) {
                    $datum = new \stdClass();
                    $date = null;
                    if ($i === 0) $date = $now->greaterThan(Carbon::parse($electionEndDay->toDateString()))
                        ? Carbon::parse($electionEndDay->toDateString()) : $now;
                    else $date = $now->greaterThan(Carbon::parse($electionEndDay->toDateString()))
                        ? Carbon::parse($electionEndDay->toDateString())->subDays($i)
                        : Carbon::parse($now->toDateString())
                                ->subDays($i);
                    $datum->name = $i === 0 && !$now->greaterThan(Carbon::parse($electionEndDay->toDateString()))
                        ? "Today" : $date->format('D');
                    $datum->Votes = Vote::whereDate('created_at', $date->toDateString())->count();
                    array_push($data, $datum);
                }
            } else {
                $data = [];
                $datum = new \stdClass();
                $datum->name = "Today";
                $datum->Votes = Vote::whereDate('created_at', $now->toDateString())->count();
                array_push($data, $datum);
            }
        else {
            $data = [];
            for ($i = 0; $i < $differenceInDays + 1; $i++) {
                $datum = new \stdClass();
                $date = null;
                if ($i === 0) $date = $now->greaterThan(Carbon::parse($electionEndDay->toDateString()))
                    ? Carbon::parse($electionEndDay->toDateString()) : $now;
                else $date = $now->greaterThan(Carbon::parse($electionEndDay->toDateString()))
                    ? Carbon::parse($electionEndDay->toDateString())->subDays($i)
                    : Carbon::parse($now->toDateString())
                            ->subDays($i);
                $datum->name = $i === 0 && !$now->greaterThan(Carbon::parse($electionEndDay->toDateString())) ? "Today"
                    : $date->format('D');
                $datum->Votes = Vote::whereDate('created_at', $date->toDateString())->count();
                array_push($data, $datum);
            }
        }
        $data = array_reverse($data);
        return response(["data" => $data]);
    }

}

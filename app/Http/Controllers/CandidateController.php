<?php

namespace App\Http\Controllers;

use App\Candidate;
use App\Election;
use App\Events\CandidateCreated;
use App\LocalGovernment;
use App\Party;
use App\State;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index($perPage = 20)
    {
        $currentElection =
            Election::where('status', 'pending')->orWhere('status', 'ongoing')->orWhere('status', 'completed')
                    ->orderBy('id', 'desc')->first();
        $candidates =
            Candidate::where('election_id', $currentElection->id)->orderBy('party_name', 'asc')->orderBy('role', 'asc')
                     ->paginate($perPage);
        $detailedCandidates = $candidates->map(function (Candidate $candidate)
        {
            $party = Party::find($candidate->party_id);
            $candidate->party_logo = $party->logo;
            return $candidate;
        });
        return response([
            "candidates"    => $detailedCandidates,
            "current_page"  => $candidates->currentPage(),
            "last_page"     => $candidates->lastPage(),
            "per_page"      => $candidates->perPage(),
            "next_page_url" => $candidates->nextPageUrl(),
            "total_results" => $candidates->total()
        ]);
    }

    /**
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function create(Request $request, $userId)
    {
        $user = User::find($userId);
        $election = Election::where('status', 'pending')->orderBy('id', 'desc')->first();
        $party = Party::find($request->party_id);
        $roles = json_decode($user->roles);
        if (is_null($user)) return response([
            'completed' => false,
            'err'       => 'userNotExist'
        ]);
        else if (in_array("official", $roles)) return response([
            'completed' => false,
            'err'       => 'officialCantBeCandidate'
        ]);
        else if (in_array("candidate", $roles)) return response([
            'completed' => false,
            'err'       => 'alreadyCandidate'
        ]);
        else if (is_null($election)) return response([
            'completed' => false,
            'err'       => 'noPendingElection'
        ]);
        else if (is_null($party)) return response([
            'completed' => false,
            'err'       => 'partyNotExist'
        ]);
        else if (Carbon::parse($user->dob)->age < 35) return response([
            'completed' => false,
            'err'       => "notOfAge"
        ]);
        else
        {
            $candidatePicture = null;
            try
            {
                $candidatePicture = $request->file('candidate_picture')->store('candidate-pictures', 'public');
                $candidate = new Candidate;
                $candidate->user_id = $user->id;
                $candidate->name = $user->name;
                $candidate->party_id = $request->party_id;
                $candidate->candidate_picture = $candidatePicture;
                $candidate->role = $request->role;
                $candidate->election_id = $election->id;
                $candidate->party_name = $party->name;
                $user->roles = json_encode([
                    "voter",
                    "candidate"
                ]);
                foreach (Candidate::where('election_id', $election->id)->cursor() as $existingCandidate)
                {
                    if ($existingCandidate->role == $candidate->role &&
                        $existingCandidate->party_id == $candidate->party_id)
                    {
                        return response([
                            "completed" => false,
                            "err"       => "candidateConflictingRole"
                        ]);
                    }
                }
                $user->save();
                $candidate->save();
            } catch (\Illuminate\Database\QueryException $e)
            {
                Storage::disk('public')->delete($candidatePicture);
                return response(["db_error" => $e->getMessage()]);
            } catch (\Exception $e)
            {
                Storage::disk('public')->delete($candidatePicture);
                throw $e;
            }
            event(new CandidateCreated($candidate));
            return response(["completed" => true]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function read($id)
    {
        $candidate = Candidate::find($id);
        $party = Party::find($candidate->party_id);
        if (is_null($candidate)) return response(["candidate" => null]);
        $user = User::find($candidate->user_id);
        $candidate->name = $user->name;
        $candidate->email = $user->email;
        $candidate->party_logo = $party->logo;
        return response(["candidate" => $candidate]);
    }

    public function update(Request $request, $id)
    {
        $candidate = Candidate::find($id);
        if (is_null($candidate)) return response([
            'completed' => false,
            'err'       => 'candidateNotExist'
        ]);
        else
        {
            $election = Election::where('status', 'pending')->orderBy('id', 'desc')->first();
            $party = Party::find($request->party_id);
            if (is_null($election)) return response([
                'completed' => false,
                'err'       => 'noPendingElection'
            ]);
            else if (is_null($party)) return response([
                'completed' => false,
                'err'       => 'partyNotExist'
            ]);
            else
            {
                $candidatePicture = null;
                try
                {
                    if (!is_null($request->file('candidate_picture')))
                    {
                        $candidatePicture = $request->file('candidate_picture')->store('candidate-pictures', 'public');
                        $candidate->candidate_picture = $candidatePicture;
                    }
                    $candidate->party_id = $request->party_id;
                    $candidate->role = $request->role;
                    $candidate->election_id = $election->id;
                    foreach (Candidate::where('election_id', $election->id)->cursor() as $existingCandidate)
                    {
                        if ($existingCandidate->role == $candidate->role &&
                            $existingCandidate->party_id == $candidate->party_id &&
                            $existingCandidate->id != $candidate->id)
                        {
                            return response([
                                "completed" => false,
                                "err"       => "candidateConflictingRole"
                            ]);
                        }
                    }
                    $candidate->save();
                } catch (\Illuminate\Database\QueryException $e)
                {
                    Storage::disk('public')->delete($candidatePicture);
                    return response(["db_error" => $e->getMessage()]);
                } catch (\Exception $e)
                {
                    Storage::disk('public')->delete($candidatePicture);
                    throw $e;
                }
                return response(["completed" => true]);
            }
        }
    }

    public function delete($id)
    {
        $candidate = Candidate::find($id);
        if (is_null($candidate)) return response(["candidate" => null]);
        $user = User::find($candidate->user_id);
        $user->roles = json_encode(["voter"]);
        $user->save();
        $candidate->delete();
        return response(["completed" => true]);
    }

    public function makeCandidate()
    {
        $candidate = factory(Candidate::class, 20)->create();
        return response(["candidate" => $candidate]);
    }

    public function search($perPage = 20, $needle)
    {
        $candidates = Candidate::where('name', 'like', "%{$needle}%")->orWhere('role', 'like', "%{$needle}%")
                               ->orWhere('party_name', 'like', "%{$needle}%")->orderBy('name', 'asc')
                               ->paginate($perPage);
        $detailedCandidates = $candidates->map(function ($candidate)
        {
            $party = Party::find($candidate->party_id);
            $candidate->party_logo = $party->logo;
            return $candidate;
        });
        return response([
            "candidates"    => $detailedCandidates,
            "current_page"  => $candidates->currentPage(),
            "last_page"     => $candidates->lastPage(),
            "per_page"      => $candidates->perPage(),
            "next_page_url" => $candidates->nextPageUrl(),
            "total_results" => $candidates->total()
        ]);
    }

    public function indexNonCandidates($perPage = 20)
    {
        $users = User::whereJsonDoesntContain('roles', 'candidate')->whereJsonDoesntContain('roles', 'official')
                     ->orderBy('name', 'asc')->paginate($perPage);
        if (!isset($_GET["page"]))
        {
            $states = State::all();
            $lgas = LocalGovernment::all();
            $lgas = $lgas->map(function ($lga)
            {
                $lga->state = State::where('state_id', $lga->state_id)->first()->name;
                return $lga;
            });
            $usersWithStates = $users->map(function (User $user)
            {
                $lga = $user->lga_id;
                $lga = LocalGovernment::find($lga);
                $state = State::find($lga->state_id);
                $user->state = $state->name;
                $user->lga = $lga->name;
                $user->age = [
                    "dob_string" => Carbon::parse($user->dob)->format('jS M, Y'),
                    "age"        => Carbon::parse($user->dob)->age
                ];
                return $user;
            });
            return response([
                "users"         => $usersWithStates,
                "current_page"  => $users->currentPage(),
                "last_page"     => $users->lastPage(),
                "per_page"      => $users->perPage(),
                "next_page_url" => $users->nextPageUrl(),
                "total_results" => $users->total(),
                "states"        => $states,
                "lgas"          => $lgas,
            ]);
        }
        return $this->returnUsersWithStates($users);
    }

    public function nonCandidatesState($id, $perPage = 20)
    {
        $users = User::where('state_id', $id)->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')->paginate($perPage);
        return $this->returnUsersWithStates($users);
    }

    public function nonCandidatesLga($id, $perPage = 20)
    {
        $users = User::where('lga_id', $id)->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')->paginate($perPage);
        return $this->returnUsersWithStates($users);
    }

    public function nonCandidateSearch($search, $perPage)
    {
        $users = null;
        if (isset($_GET["filter_by"]))
        {
            if ($_GET["filter_by"] == "state")
            {
                $users = User::where(function ($query) use ($search)
                {
                    $query->where('name', 'like', '%' . $search . '%')->orWhere('gender', 'like', '%' . $search . '%')
                          ->orWhere('marital_status', 'like', '%' . $search . '%');
                })->where('state_id', (int)$_GET["filter_value"])->whereJsonDoesntContain('roles', 'candidate')
                             ->whereJsonDoesntContain('roles', 'official')->orderBy('name', 'asc')->paginate($perPage);
            }
            else
            {
                $users = User::where(function ($query) use ($search)
                {
                    $query->where('name', 'like', '%' . $search . '%')->orWhere('gender', 'like', '%' . $search . '%')
                          ->orWhere('marital_status', 'like', '%' . $search . '%');
                })->where('lga_id', (int)$_GET["filter_value"])->whereJsonDoesntContain('roles', 'candidate')
                             ->whereJsonDoesntContain('roles', 'official')->orderBy('name', 'asc')->paginate($perPage);
            }
        }
        else
        {
            $users = User::where(function ($query) use ($search)
            {
                $query->where('name', 'like', '%' . $search . '%')->orWhere('gender', 'like', '%' . $search . '%')
                      ->orWhere('marital_status', 'like', '%' . $search . '%');
            })->whereJsonDoesntContain('roles', 'official')->whereJsonDoesntContain('roles', 'candidate')
                         ->paginate($perPage);
        }
        return $this->returnUsersWithStates($users);
    }

    private function returnUsersWithStates($users)
    {
        $usersWithStates = $users->map(function (User $user)
        {
            $lga = $user->lga_id;
            $lga = LocalGovernment::find($lga);
            $state = State::find($lga->state_id);
            $user->state = $state->name;
            $user->lga = $lga->name;
            $user->age = [
                "dob_string" => Carbon::parse($user->dob)->format('jS M, Y'),
                "age"        => Carbon::parse($user->dob)->age
            ];
            return $user;
        });
        return response([
            "users"         => $usersWithStates,
            "current_page"  => $users->currentPage(),
            "last_page"     => $users->lastPage(),
            "per_page"      => $users->perPage(),
            "next_page_url" => $users->nextPageUrl(),
            "total_results" => $users->total()
        ]);
    }

    public function initCreate($id)
    {
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')->orWhere('status', 'completed')
                            ->orderBy('id', 'desc')->first();
        $user = User::find($id);
        if (is_null($user)) return response(["user" => null]);
        $parties = Party::orderBy('name', 'asc')->get();
        return response([
            "election" => $election,
            "user"     => $user,
            "parties"  => $parties
        ]);
    }

    public function initEdit($id)
    {
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')->orWhere('status', 'completed')
                            ->orderBy('id', 'desc')->first();
        $parties = Party::orderBy('name', 'asc')->get();
        $candidate = Candidate::find($id);
        if (is_null($candidate)) return response(["candidate" => null]);
        $candidate->party_logo = Party::find($candidate->party_id)->logo;
        return response([
            "election"  => $election,
            "candidate" => $candidate,
            "parties"   => $parties
        ]);
    }

}

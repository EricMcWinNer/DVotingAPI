<?php

namespace App\Http\Controllers;

use App\Candidate;
use App\Election;
use App\Events\CandidateCreated;
use App\LocalGovernment;
use App\Party;
use App\State;
use App\User;
use App\Utils\UserHelper;
use Carbon\Carbon;
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
        $candidates =
            Candidate::with('party')->orderBy('party_name', 'asc')->orderBy('role', 'asc')->paginate($perPage);
        return response([
            "candidates" => $candidates,
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
        if (is_null($user)) return $this->returnError("userNotExist");
        else if (!UserHelper::isOnlyVoter($user)) return $this->returnError("officialCantBeCandidate");
        else if (is_null($election)) return $this->returnError("noPendingElection");
        else if (is_null($party)) return $this->returnError("partyNotExist");
        else if (Carbon::parse($user->dob["dob"])->age < 35) return $this->returnError("notOfAge");
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
                $user = UserHelper::makeCandidate($user);
                foreach (Candidate::where('election_id', $election->id)->cursor() as $existingCandidate)
                {
                    if ($existingCandidate->role == $candidate->role &&
                        $existingCandidate->party_id == $candidate->party_id)
                    {
                        return $this->returnError("candidateConflictingRole");
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
        $candidate = Candidate::with('party')->where('id', $id)->first();
        if (is_null($candidate)) return response(["candidate" => null]);
        return response(["candidate" => $candidate]);
    }

    public function update(Request $request, $id)
    {
        $candidate = Candidate::find($id);
        if (is_null($candidate)) return $this->returnError("candidateNotExist");
        else
        {
            $election = Election::where('status', 'pending')->orderBy('id', 'desc')->first();
            $party = Party::find($request->party_id);
            if (is_null($election)) return $this->returnError("noPendingElection");
            else if (is_null($party)) return $this->returnError("partyNotExist");
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
                    $candidate->party_name = $party->name;
                    foreach (Candidate::where('election_id', $election->id)->cursor() as $existingCandidate)
                    {
                        if ($existingCandidate->role == $candidate->role &&
                            $existingCandidate->party_id == $candidate->party_id &&
                            $existingCandidate->id != $candidate->id)
                        {
                            return $this->returnError("candidateConflictingRole");
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
        $user = UserHelper::makeVoter($user);
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
        $candidates =
            Candidate::with('party')->where('name', 'like', "%{$needle}%")->orWhere('role', 'like', "%{$needle}%")
                     ->orWhere('party_name', 'like', "%{$needle}%")->orderBy('name', 'asc')->paginate($perPage);
        return response([
            "candidates" => $candidates,
        ]);
    }

    public function indexNonCandidates($perPage = 20)
    {
        $users = User::with('lga.state')->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')->whereJsonDoesntContain('roles', 'officer')
                     ->orderBy('name', 'asc')->paginate($perPage);
        if (!isset($_GET["page"]))
        {
            $states = State::all();
            $lgas = LocalGovernment::with('state')->orderBy('name', 'asc')->get();
            return response([
                "users"  => $users,
                "states" => $states,
                "lgas"   => $lgas,
            ]);
        }
        return response([
            "users" => $users,
        ]);
    }

    public function nonCandidatesState($id, $perPage = 20)
    {
        $users = User::with('lga.state')->where('state_id', $id)->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')->paginate($perPage);
        return response(["users" => $users]);
    }

    public function nonCandidatesLga($id, $perPage = 20)
    {
        $users = User::with('lga.state')->where('lga_id', $id)->whereJsonDoesntContain('roles', 'candidate')
                     ->whereJsonDoesntContain('roles', 'official')->paginate($perPage);
        return response(["users" => $users]);
    }

    public function nonCandidateSearch($search, $perPage)
    {
        $users = null;
        if (isset($_GET["filter_by"]))
        {
            if ($_GET["filter_by"] == "state")
            {
                $users = User::with('lga.state')->where(function ($query) use ($search)
                {
                    $query->where('name', 'like', '%' . $search . '%')->orWhere('gender', 'like', '%' . $search . '%')
                          ->orWhere('marital_status', 'like', '%' . $search . '%');
                })->where('state_id', (int)$_GET["filter_value"])->whereJsonDoesntContain('roles', 'candidate')
                             ->whereJsonDoesntContain('roles', 'official')->orderBy('name', 'asc')->paginate($perPage);
            }
            else
            {
                $users = User::with('lga.state')->where(function ($query) use ($search)
                {
                    $query->where('name', 'like', '%' . $search . '%')->orWhere('gender', 'like', '%' . $search . '%')
                          ->orWhere('marital_status', 'like', '%' . $search . '%');
                })->where('lga_id', (int)$_GET["filter_value"])->whereJsonDoesntContain('roles', 'candidate')
                             ->whereJsonDoesntContain('roles', 'official')->orderBy('name', 'asc')->paginate($perPage);
            }
        }
        else
        {
            $users = User::with('lga.state')->where(function ($query) use ($search)
            {
                $query->where('name', 'like', '%' . $search . '%')->orWhere('gender', 'like', '%' . $search . '%')
                      ->orWhere('marital_status', 'like', '%' . $search . '%');
            })->whereJsonDoesntContain('roles', 'official')->whereJsonDoesntContain('roles', 'candidate')
                         ->paginate($perPage);
        }
        return response(["users" => $users]);
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
        $candidate = Candidate::with('party')->where('id', $id)->first();
        if (is_null($candidate)) return response(["candidate" => null]);
        return response([
            "election"  => $election,
            "candidate" => $candidate,
            "parties"   => $parties
        ]);
    }

    private function returnError($err)
    {
        return response([
            "completed" => false,
            "err"       => $err
        ]);
    }

}

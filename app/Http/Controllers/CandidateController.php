<?php

namespace App\Http\Controllers;

use App\Candidate;
use App\Election;
use App\Events\CandidateCreated;
use App\Party;
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
        $candidates = Candidate::paginate($perPage);
        $detailedCandidates =
            $candidates->map(function (Candidate $candidate)
            {
                $user = User::find($candidate->user_id);
                $party = Party::find($candidate->party_id);
                $candidate->user_info = [
                    "name"  => $user->name,
                    "email" => $user->email,
                ];
                $candidate->party = [
                    "name" => $party->name,
                    "logo" => $party->logo
                ];
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
        $election = Election::where('status', 'pending')
                            ->orderBy('id', 'desc')
                            ->first();
        $party = Party::find($request->party_id);
        if (is_null($user)) return response(['err' => 'userNotExist']);
        else if (is_null($election)) return response(['err' => 'noPendingElection']);
        else if (is_null($party)) return response(['err' => 'partyNotExist']);
        else
        {
            $candidatePicture = null;
            try
            {
                $candidatePicture = $request->file('candidatePicture')
                                            ->store('candidate-pictures',
                                                'public');
                $candidate = new Candidate;
                $candidate->user_id = $user->id;
                $candidate->party_id = $request->party_id;
                $candidate->candidate_picture = $candidatePicture;
                $candidate->role = $request->role;
                $candidate->election_id = $election->id;
                foreach (Candidate::where('election_id',
                    $election->id)
                                  ->cursor() as $existingCandidate)
                {
                    if ($existingCandidate->role ==
                        $candidate->role &&
                        $existingCandidate->party_id ==
                        $candidate->party_id)
                    {
                        return response(["err" => "candidateConflictingRole"]);
                    }
                }
                $candidate->save();
            } catch (\Illuminate\Database\QueryException $e)
            {
                Storage::disk('public')
                       ->delete($candidatePicture);
                return response(["db_error" => $e->getMessage()]);
            } catch (\Exception $e)
            {
                Storage::disk('public')
                       ->delete($candidatePicture);
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
        $user = User::find($candidate->user_id);
        $candidate->name = $user->name;
        $candidate->email = $user->email;
        return response(["candidate" => $candidate]);
    }

    public function update(Request $request, $id)
    {
        $candidate = Candidate::find($id);
        if (is_null($candidate)) return response(['err' => 'candidateNotExist']);
        else
        {
            $election = Election::where('status', 'pending')
                                ->orderBy('id', 'desc')
                                ->first();
            $party = Party::find($request->party_id);
            if (is_null($election)) return response(['err' => 'noPendingElection']);
            else if (is_null($party)) return response(['err' => 'partyNotExist']);
            else
            {
                $candidatePicture = null;
                try
                {
                    if(!is_null($request->file('candidate_picture')))
                    {
                        $candidatePicture =
                            $request->file('candidate_picture')
                                    ->store('candidate-pictures',
                                        'public');
                        $candidate->candidate_picture = $candidatePicture;
                    }
                    $candidate->party_id = $request->party_id;
                    $candidate->role = $request->role;
                    $candidate->election_id = $election->id;
                    foreach (Candidate::where('election_id',
                        $election->id)
                                      ->cursor() as
                             $existingCandidate)
                    {
                        if ($existingCandidate->role ==
                            $candidate->role &&
                            $existingCandidate->party_id ==
                            $candidate->party_id)
                        {
                            return response(["err" => "candidateConflictingRole"]);
                        }
                    }
                    $candidate->save();
                } catch (\Illuminate\Database\QueryException $e)
                {
                    Storage::disk('public')
                           ->delete($candidatePicture);
                    return response(["db_error" => $e->getMessage()]);
                } catch (\Exception $e)
                {
                    Storage::disk('public')
                           ->delete($candidatePicture);
                    throw $e;
                }
                return response(["completed" => true]);
            }
        }
    }

    public function delete($id)
    {
        $candidate = Candidate::find($id);
        $candidate->delete();
        return response(["completed" => "true"]);
    }


}

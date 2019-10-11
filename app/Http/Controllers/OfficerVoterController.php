<?php

namespace App\Http\Controllers;

use App\OfficerRegister;
use App\User;
use App\Utils\UserHelper;
use App\Utils\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
$election = app(ElectionController::class)->getCurrentElection();

class OfficerVoterController extends Controller
{
    public function registerVoter(Request $request)
    {
        try
        {
            $officer = $request->user();
            $election = app(ElectionController::class)->getCurrentElection();
            $fields = json_decode($request->userInfo, true);
            $fields = array_map(function ($value)
            {
                return trim($value);
            }, $fields);
            $fields["gender"] = UserHelper::GENDERS[(int)$fields["gender"]];
            $fields["lastName"] = ucwords($fields["lastName"]);
            $fields["otherNames"] = ucwords($fields["otherNames"]);
            $fields["maritalStatus"] = UserHelper::MARITALSTATUSES[(int)$fields["maritalStatus"]];
            $profilePicture = null;
            if(!is_null($election) && $election->status === "ongoing") {
                return response([
                    "isValid" => false,
                    "field" => "ongoingElection"
                ]);
            }
            if ($request->hasFile('picture')) $profilePicture =
                $request->file('picture')->store('profile-picture', 'public');
            else
            {
                $profilePicture = "profile-picture/image_" . time() . ".jpeg";
                Storage::disk('public')
                       ->put("{$profilePicture}", base64_decode(Utility::extractDataFromWebCamBase64($request->picture)));
            }
            $user = new User;
            $user->name = $fields['lastName'] . " " . $fields["otherNames"];
            $user->email = $fields['email'];
            $user->password = Hash::make($fields['password']);
            $user->lga_id = $fields['lgaOfOrigin'];
            $user->state_id = $fields['stateOfOrigin'];
            $user->address1 = $fields['address1'];
            $user->address2 = $fields['address2'];
            $user->dob = Carbon::parse($fields['dob']);
            $user->gender = $fields['gender'];
            $user->occupation = $fields['occupation'];
            $user->marital_status = $fields['maritalStatus'];
            $user->phone_number = $fields['phoneNumber'];
            $user->left_index = $fields['leftIndex'];
            $user->left_thumb = $fields['leftThumb'];
            $user->right_index = $fields['rightIndex'];
            $user->right_thumb = $fields['rightThumb'];
            $user = UserHelper::makeVoter($user);
            $user->picture = $profilePicture;
            $user->save();
            $officerRegister = new OfficerRegister;
            $officerRegister->officer_id = $officer->id;
            $officerRegister->voter_id = $user->id;
            $officerRegister->save();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062)
            {
                return response([
                    "isValid" => false,
                    "field"   => $e->getMessage()
                ]);
            }
            else
                return response(["exception" => $e->getMessage()]);
        }
        catch (\Exception $e)
        {
            return response(["exception" => $e->getMessage()]);
        }
        return response(["status" => "success"]);
    }

    public function getRegisteredVoters(Request $request, $perPage = 20)
    {
        $user = $request->user();
        $users = OfficerRegister::with(['voter.lga.state'])->where('officer_id', $user->id)
                                ->orderBy('created_at', 'desc')->paginate($perPage);
        $data = collect($users->items())->map(function ($item)
        {
            return $item->voter;
        });
        $paginator = new \stdClass();
        $paginator->data = $data;
        $paginator->current_page = $users->currentPage();
        $paginator->last_page = $users->lastPage();
        $paginator->per_page = $users->perPage();
        $paginator->total = $users->total();
        return response(["voters" => $paginator]);
    }

    public function read(Request $request, $id)
    {
        $officer = $request->user();
        $voter = OfficerRegister::with('voter.lga.state')->where('officer_id', $officer->id)
                                ->where('voter_id', $id)->first();
        return response(["voter" => $voter]);
    }

    public function edit(Request $request, $id)
    {
        try
        {
            $user = User::find($id);
            $fields = json_decode($request->userInfo, true);
            $fields = array_map(function ($value)
            {
                return trim($value);
            }, $fields);
            $fields["gender"] = UserHelper::GENDERS[(int)$fields["gender"]];
            $fields["lastName"] = ucwords($fields["lastName"]);
            $fields["otherNames"] = ucwords($fields["otherNames"]);
            $fields["maritalStatus"] = UserHelper::MARITALSTATUSES[(int)$fields["maritalStatus"]];
            if ($request->hasFile('picture') || Utility::validateWebCamBase64($request->picture))
            {
                if ($request->hasFile('picture')) $user->picture =
                    $request->file('picture')->store('profile-picture', 'public');
                else
                {
                    $profilePicture = "profile-picture/image_" . time() . ".jpeg";
                    Storage::disk('public')
                           ->put("{$profilePicture}", base64_decode(Utility::extractDataFromWebCamBase64($request->picture)));
                    $user->picture = $profilePicture;
                }
            }
            $user->name = $fields['lastName'] . " " . $fields["otherNames"];
            $user->email = $fields['email'];
            $user->lga_id = $fields['lgaOfOrigin'];
            $user->state_id = $fields['stateOfOrigin'];
            $user->address1 = $fields['address1'];
            $user->address2 = $fields['address2'];
            $user->dob = Carbon::parse($fields['dob']);
            $user->gender = $fields['gender'];
            $user->occupation = $fields['occupation'];
            $user->marital_status = $fields['maritalStatus'];
            $user->phone_number = $fields['phoneNumber'];
            $user->push();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062)
            {
                return response([
                    "isValid" => false,
                    "field"   => "email address"
                ]);
            }
            else
                return response(["exception" => $e->getMessage()]);
        }
        catch (\Exception $e)
        {
            return response(["exception" => $e->getMessage()]);
        }
        return response(["status" => "success"]);
    }

    public function searchVoters(Request $request, $searchNeedle, $perPage = 20)
    {
        $users = OfficerRegister::with([
            'voter' => function ($query) use ($searchNeedle)
            {
                $query->with('lga.state')->where('name', 'like', "%{$searchNeedle}%")
                      ->orderBy('name', 'asc');
            },
        ])->where('officer_id', $request->user()->id)->paginate($perPage);
        $data = collect($users->items())->filter(function ($item)
        {
            return !is_null($item->voter);
        })->map(function ($item)
        {
            return $item->voter;
        });
        if(count($data->toArray()) === 1)
        {
            foreach($data as $key)
            {
                $data = [$key];
            }
        }
        $paginator = new \stdClass();
        $paginator->data = $data;
        $paginator->current_page = $users->currentPage();
        $paginator->last_page = $users->lastPage();
        $paginator->per_page = $users->perPage();
        $paginator->total = $users->total();
        return response([
            "voters" => $paginator,

        ]);
    }
}

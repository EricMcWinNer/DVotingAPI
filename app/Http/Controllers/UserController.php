<?php

namespace App\Http\Controllers;

use App\LocalGovernment;
use App\State;
use App\User;
use App\RegistrationPin;
use App\Utils\RegistrationPinHelper;
use App\Utils\UserHelper;
use App\Utils\Utility;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    //

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function registerPrivilegedUsers(Request $request)
    {
        try
        {
            $fields = json_decode($request->userInfo, true);
            $URI = $request->path();
            $fields = array_map(function ($value)
            {
                return trim($value);
            }, $fields);
            $registrationPin = RegistrationPin::where('content', $fields['confirmationPin'])->first();
            if (strpos($URI, "official") !== false)
            {
                $validity = RegistrationPinHelper::validateOfficialPin($registrationPin);
                if ($validity === false) return response([
                    "isValid" => false,
                    "field"   => "confirmationPin"
                ]);
                if (is_null($validity)) if ($validity === false) return response([
                    "isValid" => false,
                    "field"   => "confirmationPinUsed"
                ]);
            }
            else
            {
                $validity = RegistrationPinHelper::validateOfficerPin($registrationPin);
                if ($validity === false) return response([
                    "isValid" => false,
                    "field"   => "confirmationPin"
                ]);
                if (is_null($validity)) if ($validity === false) return response([
                    "isValid" => false,
                    "field"   => "confirmationPinUsed"
                ]);
            }

            $fields["gender"] = UserHelper::GENDERS[(int)$fields["gender"]];
            $fields["lastName"] = ucwords($fields["lastName"]);
            $fields["otherNames"] = ucwords($fields["otherNames"]);
            $fields["maritalStatus"] = UserHelper::MARITALSTATUSES[(int)$fields["maritalStatus"]];
            $profilePicture = null;
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
            if (strpos($URI, "official") !== false) $user = UserHelper::makeOfficial($user);
            else
                $user = UserHelper::makeOfficer($user);
            $user->picture = $profilePicture;
            $registrationPin->date_used = Carbon::now()->toDateTimeString();
            $user->save();
            $registrationPin->used_by = $user->id;
            $registrationPin->save();
        } catch (\Illuminate\Database\QueryException $e)
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
        } catch (\Exception $e)
        {
            return response(["exception" => $e->getMessage()]);
        }
        return response(["status" => "success"]);
    }

    /**
     * @param $count
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function makeVoters($count)
    {
        $itr = 0;
        $maxTries = 100;
        $users = null;
        while (true)
        {
            try
            {
                $users = factory(User::class, (int)$count)->make();
                foreach ($users as $user)
                {
                    $user->save();
                }

            } catch (\Illuminate\Database\QueryException $e)
            {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062)
                {
                    if (++$itr == $maxTries) throw $e;
                }
                else
                    throw $e;
            } catch (\Exception $e)
            {
                throw $e;
            }
        }
        return response($users);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setFakeUsersStates()
    {
        $users = User::all();
        $users = $users->reject(function ($user)
        {
            return $user->state_id !== 0;
        });
        foreach ($users as $user)
        {
            $user->state_id = LocalGovernment::find($user->lga_id)->state_id;
            $user->save();
        }
        return response(["users" => User::all()]);
    }


}

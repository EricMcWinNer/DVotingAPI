<?php

namespace App\Http\Controllers;

use App\LocalGovernment;
use App\State;
use App\User;
use App\RegistrationPin;
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
    public function registerOfficial(Request $request)
    {
        $genders = [
            'male',
            'female'
        ];
        $maritalStatus = [
            'married',
            'single',
            'divorced',
            'widowed'
        ];
        $fields = json_decode($request->userInfo, true);
        $fields = array_map(function ($value)
        {
            return trim($value);
        }, $fields);
        $registrationPin = RegistrationPin::where('content',
            $fields['confirmationPin'])
                                          ->first();
        if (!is_null(User::where('email', $fields['email'])
                         ->first())) return response([
            "isValid" => false,
            "field"   => "emailExists"
        ]);
        if (count($registrationPin) != 1 ||
            !is_null($registrationPin->date_used)) return response([
            'isValid' => false,
            'field'   => 'confirmation pin'
        ]);
        $fields["gender"] = $genders[(int)$fields["gender"]];
        $fields["lastName"] = ucwords($fields["lastName"]);
        $fields["otherNames"] = ucwords($fields["otherNames"]);
        $fields["maritalStatus"] =
            $maritalStatus[(int)$fields["maritalStatus"]];
        $profilePicture = $request->file('picture')
                                  ->store('profile-picture',
                                      'public');
        $user = new User;
        $user->name =
            $fields['lastName'] . " " . $fields["otherNames"];
        $user->email = $fields['email'];
        $user->password = Hash::make($fields['password']);
        $user->lga_id = $fields['lgaOfOrigin'];
        $user->state_id = $fields['stateOfOrigin'];
        $user->address1 = $fields['address1'];
        $user->address2 = $fields['address2'];
        $user->dob = $fields['dob'];
        $user->gender = $fields['gender'];
        $user->occupation = $fields['occupation'];
        $user->marital_status = $fields['maritalStatus'];
        $user->phone_number = $fields['phoneNumber'];
        $user->roles = json_encode([
            "voter",
            "official"
        ]);
        $user->picture = $profilePicture;
        $registrationPin->date_used = Carbon::now()
                                            ->toDateTimeString();
        $registrationPin->save();
        $user->save();
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
            $user->state_id =
                LocalGovernment::find($user->lga_id)->state_id;
            $user->save();
        }
        return response(["users" => User::all()]);
    }


}

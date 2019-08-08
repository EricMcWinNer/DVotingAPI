<?php

namespace App\Http\Controllers;

use App\User;
use App\RegistrationPin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //

    public function registerOfficial(Request $request)
    {
        $genders = ['male', 'female'];
        $maritalStatus = ['married', 'single', 'divorced', 'widowed'];
        $fields = json_decode($request->userInfo, true);
        $fields = array_map(function ($value) {
            return trim($value);
        }, $fields);
        $registrationPin = RegistrationPin::where('content', $fields['confirmationPin'])->first();
        if (!is_null(User::where('email', $fields['email'])->first()))
            return response(["isValid" => false, "field" => "emailExists"]);
        if (count($registrationPin) != 1 || !is_null($registrationPin->date_used))
            return response([
                'isValid' => false,
                'field' => 'confirmation pin'
            ]);
        $fields["gender"] = $genders[(int)$fields["gender"]];
        $fields["lastName"] = ucwords($fields["lastName"]);
        $fields["otherNames"] = ucwords($fields["otherNames"]);
        $fields["maritalStatus"] = $maritalStatus[(int)$fields["maritalStatus"]];
        $profilePicture = $request->file('picture')->store('profile-picture', 'public');
        $user = new User;
        $user->name = $fields['lastName'] . " " . $fields["otherNames"];
        $user->email = $fields['email'];
        $user->password = Hash::make($fields['password']);
        $user->lga_id = $fields['lgaOfOrigin'];
        $user->address1 = $fields['address1'];
        $user->address2 = $fields['address2'];
        $user->dob = $fields['dob'];
        $user->gender = $fields['gender'];
        $user->occupation = $fields['occupation'];
        $user->marital_status = $fields['maritalStatus'];
        $user->phone_number = $fields['phoneNumber'];
        $user->roles = json_encode(["voter", "official"]);
        $user->picture = $profilePicture;
        $registrationPin->date_used = Carbon::now()->toDateTimeString();
        $registrationPin->save();
        $user->save();
        return response(["status" => "success"]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Utils\Utility;
use Carbon\Carbon;
use Closure;

class OfficialRegistrationValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $fields = json_decode($request->userInfo, true);
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
        $email = $fields["email"];
        if (strlen($fields["lastName"]) < 3 || count(explode(" ", $fields["lastName"])) != 1) return response([
            "isValid" => false,
            "field"   => "last name"
        ]);
        else if (strlen($fields["otherNames"]) < 6 || count(explode(" ", $fields["otherNames"])) < 2) return response([
            "isValid" => false,
            "field"   => "other names"
        ]);
        else if (!array_key_exists($fields["gender"], $genders)) return response([
            "isValid" => false,
            "field"   => "gender"
        ]);
        else if (!array_key_exists($fields["maritalStatus"], $maritalStatus)) return response([
            "isValid" => false,
            "field"   => "marital status"
        ]);
        else if (!preg_match('/^([0-9]{11,11})$/', $fields['nin'])) return response([
            "isValid" => false,
            "field"   => "invalidNIN"
        ]);
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return response([
            "isValid" => false,
            "field"   => "email"
        ]);
        else if (strlen($fields["phoneNumber"]) < 11) return response([
            "isValid" => false,
            "field"   => "phone number"
        ]);
        else if (empty($fields["dob"])) return response([
            "isValid" => false,
            "field"   => "date of birth"
        ]);
        else if (empty($fields["stateOfOrigin"])) return response([
            "isValid" => false,
            "field"   => "state of origin"
        ]);
        else if (empty($fields["lgaOfOrigin"])) return response([
            "isValid" => false,
            "field"   => "LGA"
        ]);
        else if (empty($fields["occupation"])) return response([
            "isValid" => false,
            "field"   => "occupation"
        ]);
        else if (empty($fields["address1"])) return response([
            "isValid" => false,
            "field"   => "address1"
        ]);
        else if ($fields["password"] != $fields["confirmPassword"]) return response([
            "isValid" => false,
            "field"   => "password"
        ]);
        else if (!$request->hasFile('picture')) {
            if (Utility::validateWebCamBase64($request->picture)) return $next($request);
            else return response([
                "isValid" => false,
                "field"   => $request->picture
            ]);
        } else if (!$request->file('picture')->isValid()) {
            return response([
                "isValid" => false,
                "field"   => "Profile picture"
            ]);
        } else if (!substr($request->file('picture')->getMimeType(), 0, 5) == 'image') return response([
            "isValid" => false,
            "field"   => "Profile picture"
        ]);
        else if (Carbon::parse($fields["dob"])->age < 18) return response([
            "isValid" => false,
            "field"   => "tooYoung"
        ]);
        else if (!(!empty($fields["leftIndex"]) && !is_null($fields["leftIndex"]) && Utility::validateFingerprintBase64($fields["leftIndex"])))
            return response([
                "isValid" => false,
                "field"   => "left index fingerprint"
            ]);
        else if (!(!empty($fields["leftThumb"]) && !is_null($fields["leftThumb"]) && Utility::validateFingerprintBase64($fields["leftThumb"])))
            return response([
                "isValid" => false,
                "field"   => "left thumb fingerprint"
            ]);
        else if (!(!empty($fields["rightIndex"]) && !is_null($fields["rightIndex"]) && Utility::validateFingerprintBase64($fields["rightIndex"])))
            return response([
                "isValid" => false,
                "field"   => "right index fingerprint"
            ]);
        else if (!(!empty($fields["rightThumb"]) && !is_null($fields["rightThumb"]) && Utility::validateFingerprintBase64($fields["rightThumb"])))
            return response([
                "isValid" => false,
                "field"   => "right thumb fingerprint"
            ]);
        else
            return $next($request);
    }
}

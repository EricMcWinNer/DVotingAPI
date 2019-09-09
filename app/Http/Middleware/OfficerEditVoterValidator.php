<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class OfficerEditVoterValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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
        else if (empty($fields["address2"])) return response([
            "isValid" => false,
            "field"   => "address2"
        ]);
        else if (Carbon::parse($fields["dob"])->age < 18) return response([
            "isValid" => false,
            "field"   => "tooYoung"
        ]);
        else
            return $next($request);
    }
}

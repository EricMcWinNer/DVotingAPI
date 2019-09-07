<?php
/**
 * Created by PhpStorm.
 * User: Eric McWinNEr
 * Date: 8/26/2019
 * Time: 9:27 PM
 */

namespace App\Utils;


use App\User;

class UserHelper
{
    public static function makeVoter(User $user)
    {
        $user->roles = json_encode(["voter"]);
        return $user;
    }

    public static function makeCandidate(User $user)
    {
        $user->roles = json_encode([
            "voter",
            "candidate"
        ]);
        return $user;
    }

    public static function makeOfficial(User $user)
    {
        $user->roles = json_encode([
            "voter",
            "official"
        ]);
        return $user;
    }

    public static function makeOfficer(User $user)
    {
        $user->roles = json_encode([
            "voter",
            "officer"
        ]);
        return $user;
    }

    public static function isOfficial(User $user)
    {
        $roles = json_decode($user->roles);
        return in_array("official", $roles);
    }

    public static function isOfficer(User $user)
    {
        $roles = json_decode($user->roles);
        return in_array("officer", $roles);
    }

    public static function isCandidate(User $user)
    {
        $roles = json_decode($user->roles);
        return in_array("candidate", $roles);
    }

    public static function isOnlyVoter($user)
    {
        $roles = json_decode($user->roles);
        return in_array("voter", $roles) && count($roles) == 1;
    }

    const GENDERS = [
        'male',
        'female'
    ];

    const MARITALSTATUSES = [
        'married',
        'single',
        'divorced',
        'widowed'
    ];
}
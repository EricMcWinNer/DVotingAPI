<?php
/**
 * Created by PhpStorm.
 * User: Eric McWinNEr
 * Date: 9/3/2019
 * Time: 1:07 AM
 */

namespace App\Utils;


use App\RegistrationPin;

class RegistrationPinHelper
{
    public static function validateOfficialPin(RegistrationPin $pin)
    : ?bool
    {
        if (is_null($pin)) return false;
        else if (!is_null($pin->date_used)) return null;
        else if ($pin->user_type === "official") return true;
        else return false;
    }

    public static function validateOfficerPin(RegistrationPin $pin)
    : ?bool
    {
        if (is_null($pin)) return false;
        else if (!is_null($pin->date_used)) return null;
        else if ($pin->user_type === "officer") return true;
        else return false;

    }
}
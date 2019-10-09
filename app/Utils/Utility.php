<?php
/**
 * Created by PhpStorm.
 * User: Eric McWinNEr
 * Date: 8/20/2019
 * Time: 12:20 AM
 */

namespace App\Utils;


use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Utility
{
    public static function shortTextifyNumbers(int $number)
    : string
    {
        if ($number >= 1000000000 && $number < 1000000000000) return round($number / 1000000000) .
            "B";
        else if ($number >= 1000000 && $number < 1000000000) return round($number / 1000000) . "M";
        else if ($number >= 1000 && $number < 1000000) return round($number / 1000) . "K";
        else
            return $number;
    }

    public static function dateStringParser($date)
    {
        $date = new Carbon($date);
        if ($date->isSameMinute()) return "just now";
        else if ($date->isSameHour()) return Carbon::now()->diffInMinutes($date) . " mins ago";
        else if ($date->isSameDay()) return $date->format('H:i');
        else if ($date->isYesterday()) return "yesterday, " . $date->format('H:i');
        else if ($date->isSameYear()) return $date->format("jS, M");
        else
            return $date->format("jS, M 'y");
    }


    public static function renameProfilePictures()
    {
        $fileSystem = new Filesystem();
        $files = Storage::disk('public')->files();
        for ($i = 0; $i < count($files); $i++) {
            Storage::disk('public')->move($files[$i], "profile-picture/" . ($i + 1) . ".jpg");
        }
        /*$renamedFiles = Storage::disk('public')->files('profile-picture');*/
        return response([
            "files"     => $files,
            "extension" => $fileSystem->extension($files[0])
        ]);
    }

    public static function validateWebCamBase64($string)
    : bool
    {
        if (is_null($string) || $string == "null") return false;
        $data = explode(",", $string)[1];
        $type = explode(":", explode(";", $string)[0])[1];
        if (base64_encode(base64_decode($data, true)) === $data) {
            if ($type != "image/jpeg") return false;
            else
                return true;
        } else {
            return false;
        }
    }

    public static function extractDataFromWebCamBase64($string)
    {
        return explode(",", $string)[1];
    }

    public static function roundEverythingUp($float)
    {
        $type = gettype($float);
        if ($type === "double" || $type === "float") {
            return floor($float) + 1;
        } else {
            return $float;
        }
    }
}
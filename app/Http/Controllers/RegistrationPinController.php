<?php

namespace App\Http\Controllers;

use App\RegistrationPin;
use Illuminate\Http\Request;

/**
 * Class RegistrationPinController
 * @package App\Http\Controllers
 */
class RegistrationPinController extends Controller
{
    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index($perPage = 20)
    {
        $pins = null;
        switch ($_GET["type"])
        {
            case "official":
                if ($_GET["status"] === "unused") $pins =
                    RegistrationPin::with('createdBy:id,name,email')->whereNull("date_used")
                                   ->where('user_type', "official")->paginate($perPage);
                else $pins = RegistrationPin::with([
                    'usedBy:id,name,email',
                    'createdBy:id,name,email'
                ])->whereNotNull('date_used')->where('user_type', "official")->paginate($perPage);
                break;
            case "officer":
                if ($_GET["status"] === "unused") $pins =
                    RegistrationPin::with('createdBy:id,name,email')->whereNull("date_used")
                                   ->where('user_type', "officer")->paginate($perPage);
                else $pins = RegistrationPin::with([
                    'usedBy:id,name,email',
                    'createdBy:id,name,email'
                ])->whereNotNull('date_used')->where('user_type', "officer")->paginate($perPage);
                break;
            default:
                if ($_GET["status"] === "unused") $pins = RegistrationPin::with([
                    'createdBy:id,name,email'
                ])->whereNull('date_used')->paginate($perPage);
                else $pins = RegistrationPin::with([
                    'usedBy:id,name,email',
                    'createdBy:id,name,email'
                ])->whereNotNull('date_used')->paginate($perPage);
                break;
        }
        return response(["pins" => $pins]);
    }

    /**
     * @param Request $request
     * @param $count
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function makeOfficerPins(Request $request, $count)
    {
        $successes = 0;
        while ($successes < $count)
        {
            try
            {
                $content = random_int(111111111111, 999999999999);
                $pin = new RegistrationPin;
                $pin->content = $content;
                $pin->user_type = "officer";
                $pin->created_by = $request->user()->id;
                $pin->save();
                $successes += 1;
            } catch (\Illuminate\Database\QueryException $e)
            {
                $errorCode = $e->errorInfo[1];
                if ($errorCode != 1062)
                {
                    return response(["exception" => $e->getMessage()]);
                }
            }
        }
        return response(["completed" => true]);
    }

    /**
     * @param Request $request
     * @param $count
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function makeOfficialPins(Request $request, $count)
    {
        $successes = 0;
        while ($successes < $count)
        {
            try
            {
                $content = random_int(111111111111, 999999999999);
                $pin = new RegistrationPin;
                $pin->content = $content;
                $pin->user_type = "official";
                $pin->created_by = $request->user()->id;
                $pin->save();
                $successes += 1;
            } catch (\Illuminate\Database\QueryException $e)
            {
                $errorCode = $e->errorInfo[1];
                if ($errorCode != 1062)
                {
                    return response(["exception" => $e->getMessage()]);
                }
            }
        }
        return response(["completed" => true]);
    }
}

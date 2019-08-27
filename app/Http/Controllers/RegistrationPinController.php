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
    //
    /**
     * @param $count
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function makePins($count)
    {
        $registrationPins = factory(RegistrationPin::class, (int)$count)->make();
        foreach ($registrationPins as $registrationPin) {
            $registrationPin->save();
        }
        return response($registrationPins);
    }
}

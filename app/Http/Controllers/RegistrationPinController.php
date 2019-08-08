<?php

namespace App\Http\Controllers;

use App\RegistrationPin;
use Illuminate\Http\Request;

class RegistrationPinController extends Controller
{
    //
    public function makePins($count)
    {
        $registrationPins = factory(RegistrationPin::class, (int)$count)->make();
        foreach ($registrationPins as $registrationPin) {
            $registrationPin->save();
        }
        return response($registrationPins);
    }
}

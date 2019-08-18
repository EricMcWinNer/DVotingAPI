<?php

namespace App\Http\Controllers;

use App\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartyController extends Controller
{
    public function create(Request $request)
    {
        if (is_null($request->file('partyLogo')))
            return response(["isValid" => false, "field" => "partyLogo"]);
        else if (!$request->file('partyLogo')->isValid())
            return response(["isValid" => false, "field" => "partyLogo"]);
        else if (!substr($request->file('partyLogo')->getMimeType(), 0, 5) == 'image')
            return response(["isValid" => false, "field" => "partyLogo"]);
        else {
            try {
                $partyName = ucwords($request->partyName);
                $acronym = strtoupper($request->acronym);
                $logo = $request->file('partyLogo')->store('party-logos', 'public');
                $party = new Party;
                $party->name = $partyName;
                $party->acronym = $acronym;
                $party->logo = $logo;
                $party->save();
            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                Storage::disk('public')->delete("party-logos/{$logo}");
                if ($errorCode == 1062) {
                    return response(["isValid" => false, "field" => "duplicateName"]);
                } else
                    return response(["exception" => $e->getMessage()]);
            } catch (\Exception $e) {
                return response(["exception" => $e->getMessage()]);
            }
            return response(["completed" => true]);
        }
    }

    public function getParties()
    {
        $parties = Party::orderBy('name')->get();
        return response(["parties" => $parties]);
    }

    public function getParty($id)
    {
        $party = Party::find($id);
        return response(["party" => $party]);
    }

    public function deleteParty($id)
    {
        $party = Party::find($id);
        Storage::disk('public')->delete("party-logos/{$party->logo}");
        $party->delete();
        return response(["deleted" => true]);
    }

    public function updateParty(Request $request, $id)
    {
        try {
            $partyName = ucwords($request->partyName);
            $acronym = strtoupper($request->acronym);
            $party = Party::find($id);
            $party->name = $partyName;
            $party->acronym = $acronym;
            if (!is_null($request->file('partyLogo'))) {
                if (!$request->file('partyLogo')->isValid())
                    return response(["isValid" => false, "field" => "partyLogo"]);
                else if (!substr($request->file('partyLogo')->getMimeType(), 0, 5) == 'image')
                    return response(["isValid" => false, "field" => "partyLogo"]);
                else {
                    $logo = $request->file('partyLogo')->store('party-logos', 'public');
                    $party->logo = $logo;
                }
            }
            $party->save();
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return response(["isValid" => false, "field" => "duplicateName"]);
            } else
                return response(["exception" => $e->getMessage()]);
        } catch (\Exception $e) {
            return response(["exception" => $e->getMessage()]);
        }
        return response(["completed" => true]);
    }
}

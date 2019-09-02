<?php

namespace App\Http\Controllers;

use App\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class PartyController
 * @package App\Http\Controllers
 */
class PartyController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (is_null($request->file('partyLogo'))) return response([
            "isValid" => false,
            "field"   => "partyLogo"
        ]);
        else if (!$request->file('partyLogo')->isValid()) return response([
            "isValid" => false,
            "field"   => "partyLogo"
        ]);
        else if (!substr($request->file('partyLogo')->getMimeType(), 0, 5) == 'image') return response([
            "isValid" => false,
            "field"   => "partyLogo"
        ]);
        else
        {
            try
            {
                $partyName = ucwords($request->partyName);
                $acronym = strtoupper($request->acronym);
                $logo = $request->file('partyLogo')->store('party-logos', 'public');
                $party = new Party;
                $party->name = $partyName;
                $party->acronym = $acronym;
                $party->logo = $logo;
                $party->save();
            } catch (\Illuminate\Database\QueryException $e)
            {
                $errorCode = $e->errorInfo[1];
                Storage::disk('public')->delete("party-logos/{$logo}");
                if ($errorCode == 1062)
                {
                    return response([
                        "isValid" => false,
                        "field"   => "duplicateName"
                    ]);
                }
                else
                    return response(["exception" => $e->getMessage()]);
            } catch (\Exception $e)
            {
                return response(["exception" => $e->getMessage()]);
            }
            return response(["completed" => true]);
        }
    }

    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getParties($perPage = 20)
    {
        $parties = Party::orderBy('name', 'asc')->paginate($perPage);
        return response(["parties" => $parties]);
    }

    public function search($needle, $perPage = 20)
    {
        $parties =
            Party::where('name', 'like', "%$needle%")->orWhere('acronym', 'like', "%$needle%")->orderBy('name', 'asc')
                 ->paginate($perPage);
        return response(["parties" => $parties]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getParty($id)
    {
        $party = Party::find($id);
        return response(["party" => $party]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteParty($id)
    {
        $party = Party::find($id);
        Storage::disk('public')->delete("party-logos/{$party->logo}");
        $candidates = $party->candidates;
        foreach($candidates as $candidate)
        {
            $candidate->delete();
        }
        $party->delete();
        return response(["deleted" => true]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateParty(Request $request, $id)
    {
        try
        {
            $partyName = ucwords($request->partyName);
            $acronym = strtoupper($request->acronym);
            $party = Party::find($id);
            $party->name = $partyName;
            $party->acronym = $acronym;
            if (!is_null($request->file('partyLogo')))
            {
                if (!$request->file('partyLogo')->isValid()) return response([
                    "isValid" => false,
                    "field"   => "partyLogo"
                ]);
                else if (!substr($request->file('partyLogo')->getMimeType(), 0, 5) == 'image') return response([
                    "isValid" => false,
                    "field"   => "partyLogo"
                ]);
                else
                {
                    $logo = $request->file('partyLogo')->store('party-logos', 'public');
                    $party->logo = $logo;
                }
            }
            $party->save();
        } catch (\Illuminate\Database\QueryException $e)
        {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062)
            {
                return response([
                    "isValid" => false,
                    "field"   => "duplicateName"
                ]);
            }
            else
                return response(["exception" => $e->getMessage()]);
        } catch (\Exception $e)
        {
            return response(["exception" => $e->getMessage()]);
        }
        return response(["completed" => true]);
    }
}

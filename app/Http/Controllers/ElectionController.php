<?php

namespace App\Http\Controllers;

use App\Election;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
    public function autoGenerate(Request $request)
    {
        $election = factory(Election::class)->make();
        $election->save();
        return response($election);
    }

    public function create(Request $request)
    {
        try {
            if (!$this->electionExists()) {
                $election = new Election;
                $election->name = ucwords($request->name);
                $election->start_date = Carbon::createFromFormat('D M d Y H:i:s e+', $request->start_date)->setTimezone('UTC');
                $election->end_date = Carbon::createFromFormat('D M d Y H:i:s e+', $request->end_date)->setTimeZone('UTC');
                if ($election->start_date->lessThan(Carbon::now()))
                    $election->status = 'ongoing';
                else
                    $election->status = 'pending';
                $election->created_by = $request->user()->id;
                $election->save();
                return response(["completed" => true]);
            } else {
                return response(["exists" => true]);
            }
        } catch (\Exception $e) {
            return response(["completed" => false, "message" => $e->getMessage()]);
        }
    }

    private function electionExists()
    {
        $election = Election::where('status', 'pending')
            ->orWhere('status', 'ongoing')
            ->orWhere('status', 'completed')
            ->orderBy('id', 'desc')
            ->first();
        return $election !== null;
    }

    public function getElection(Request $request)
    {
        $election = Election::where('status', 'pending')
            ->orWhere('status', 'ongoing')
            ->orWhere('status', 'completed')
            ->orderBy('id', 'desc')
            ->first();
        $createdArray = null;
        $stringDates = null;
        if (!is_null($election)) {
            $createdBy = User::find($election->created_by);
            $election->start_date = Carbon::parse($election->start_date)->setTimezone("+01:00");
            $election->end_date = Carbon::parse($election->end_date)->setTimezone("+01:00");
            $createdArray = [
                "name" => $createdBy->name,
                "email" => $createdBy->email,
            ];
            $stringDates = [
                "start_date" => Carbon::parse($election->start_date)->setTimeZone("+01:00")->toDayDateTimeString(),
                "end_date" => Carbon::parse($election->end_date)->setTimeZone("+01:00")->toDayDateTimeString()
            ];
        }
        return response([
            "isSessionValid" => "true",
            "election" => $election,
            "string_dates" => $stringDates,
            "created_by" => $createdArray
        ]);
    }

    public function getCurrentElectionMinimalInfo()
    {
        $election = Election::where('status', 'pending')
            ->orWhere('status', 'ongoing')
            ->orderBy('id', 'desc')
            ->first();
        if (is_null($election))
            $electionArray = null;
        else
            $electionArray = [
                "start_date" => Carbon::parse($election->start_date)->setTimeZone("+01:00")->toDayDateTimeString(),
                "end_date" => Carbon::parse($election->end_date)->setTimeZone("+01:00")->toDayDateTimeString(),
                "status" => $election->status,
            ];
        return response([
            "present" => !is_null($election),
            "election" => $electionArray,
        ]);
    }

    public function delete(Request $request)
    {
        if (!$this->electionExists())
            return response(["exists" => false]);
        else {
            Election::where('status', 'pending')
                ->orWhere('status', 'ongoing')
                ->orWhere('status', 'completed')
                ->orderBy('id', 'desc')
                ->first()
                ->delete();
            return response(["completed" => true]);
        }
    }

    public function edit(Request $request)
    {
        try {
            if ($this->electionExists()) {
                $election = Election::find($request->id);
                if ($election->status === 'ongoing'
                    && !Carbon::parse($election->start_date)->equalTo(Carbon::createFromFormat('D M d Y H:i:s e+', $request->input('start_date'))->setTimezone("UTC")))
                    return response(["isValid" => false, "field" => "electionAlreadyStarted"]);
                else if ($election->status === 'completed')
                    return response(["isValid" => false, "field" => 'electionAlreadyComplete']);
                else {
                    $election->name = ucwords($request->name);
                    $election->start_date = Carbon::createFromFormat('D M d Y H:i:s e+', $request->start_date)->setTimezone("UTC");
                    $election->end_date = Carbon::createFromFormat('D M d Y H:i:s e+', $request->end_date)->setTimeZone("UTC");
                    if ($election->start_date->lessThan(Carbon::now()))
                        $election->status = 'ongoing';
                    else
                        $election->status = 'pending';
                    $election->save();
                    return response(["completed" => true]);
                }
            } else {
                return response(["exists" => false]);
            }
        } catch (\Exception $e) {
            return response(["completed" => false, "message" => $e->getMessage()]);
        }
    }

    public function finalize(Request $request)
    {
        if ($this->electionExists()) {
            $currentElection = Election::where('status', 'completed')
                ->orderBy('id', 'desc')
                ->first();
            $currentElection->status = 'finalized';
            $currentElection->save();
            return response(["completed" => true]);
        } else {
            return response(["exists" => false]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Election;
use App\Events\ElectionCreated;
use App\Events\ElectionDeleted;
use App\Events\ElectionFinalized;
use App\Events\ElectionStarted;
use App\Events\ElectionUpdated;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class ElectionController
 * @package App\Http\Controllers
 */
class ElectionController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function autoGenerate(Request $request)
    {
        $election = factory(Election::class)->make();
        $election->save();
        return response($election);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            if (!$this->electionExists()) {
                $election = new Election;
                $election->name = ucwords($request->name);
                $election->start_date =
                    Carbon::createFromFormat('D M d Y H:i:s e+', $request->start_date)
                        ->setTimezone('UTC');
                $election->end_date =
                    Carbon::createFromFormat('D M d Y H:i:s e+', $request->end_date)
                        ->setTimeZone('UTC');
                $election->created_by = $request->user()->id;
                if ($election->start_date->lessThan(Carbon::now())) {
                    $election->status = 'ongoing';
                    $election->save();
                    event(new ElectionCreated($election));
                    event(new ElectionStarted($election));
                } else {
                    $election->status = 'pending';
                    $election->save();
                    event(new ElectionCreated($election));
                }
                return response(["completed" => true]);
            } else {
                return response(["exists" => true]);
            }
        } catch (\Exception $e) {
            return response([
                "completed" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * @return bool
     */
    private function electionExists()
    {
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')
            ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
        return $election !== null;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getElection(Request $request)
    {
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')
            ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
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
                "start_date" => Carbon::parse($election->start_date)->setTimeZone("+01:00")
                    ->toDayDateTimeString(),
                "end_date" => Carbon::parse($election->end_date)->setTimeZone("+01:00")
                    ->toDayDateTimeString()
            ];
        }
        return response([
            "isSessionValid" => "true",
            "election" => $election,
            "string_dates" => $stringDates,
            "created_by" => $createdArray
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCurrentElectionMinimalInfo()
    {
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')
            ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
        if (is_null($election)) {
            $electionArray = null;
        } else {
            $electionArray = [
                "start_date" => Carbon::parse($election->start_date)->setTimeZone("+01:00")
                    ->toDayDateTimeString(),
                "end_date" => Carbon::parse($election->end_date)->setTimeZone("+01:00")
                    ->toDayDateTimeString(),
                "status" => $election->status,
            ];
        }
        return response([
            "present" => !is_null($election),
            "election" => $electionArray,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if (!$this->electionExists()) {
            return response(["exists" => false]);
        } else {
            $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')
                ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
            $election->delete();
            event(new ElectionDeleted($election));
            return response(["completed" => true]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        try {
            if ($this->electionExists()) {
                $election = Election::find($request->id);
                if ($election->status === 'ongoing' && !Carbon::parse($election->start_date)
                        ->equalTo(Carbon::createFromFormat('D M d Y H:i:s e+', $request->input('start_date'))
                            ->setTimezone("UTC"))) {
                    return response([
                        "isValid" => false,
                        "field" => "electionAlreadyStarted"
                    ]);
                } else {
                    if ($election->status === 'completed') {
                        return response([
                            "isValid" => false,
                            "field" => 'electionAlreadyComplete'
                        ]);
                    } else {
                        $election->name = ucwords($request->name);
                        $election->start_date =
                            Carbon::createFromFormat('D M d Y H:i:s e+', $request->start_date)
                                ->setTimezone("UTC");
                        $election->end_date =
                            Carbon::createFromFormat('D M d Y H:i:s e+', $request->end_date)
                                ->setTimeZone("UTC");
                        if ($election->start_date->lessThan(Carbon::now())) {
                            $election->status = 'ongoing';
                        } else {
                            $election->status = 'pending';
                        }
                        $election->save();
                        event(new ElectionUpdated($election));

                        return response(["completed" => true]);
                    }
                }
            } else {
                return response(["exists" => false]);
            }
        } catch (\Exception $e) {
            return response([
                "completed" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function finalize(Request $request)
    {
        if ($this->electionExists()) {
            $currentElection =
                Election::where('status', 'completed')->orderBy('id', 'desc')->first();
            $currentElection->status = 'finalized';
            $currentElection->save();
            event(new ElectionFinalized($currentElection));
            return response(["completed" => true]);
        } else {
            return response(["exists" => false]);
        }
    }

    public function getCurrentElection()
    {
        return Election::where('status', 'pending')->orWhere('status', 'ongoing')
            ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
    }
}

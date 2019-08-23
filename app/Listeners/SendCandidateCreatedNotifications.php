<?php

namespace App\Listeners;

use App\Events\CandidateCreated;
use App\Notifications\CandidateCreatedNotification;
use App\Notifications\OfficialCandidateCreatedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendCandidateCreatedNotifications
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CandidateCreated $event
     * @return void
     */
    public function handle(CandidateCreated $event)
    {
        $newCandidate = $event->candidate;
        $candidateUser = User::find($newCandidate->user_id);
        $newCandidate->name = $candidateUser->name;
        $officials = User::whereJsonContains('roles', 'official')
                         ->get();
        Notification::send($officials, new
        OfficialCandidateCreatedNotification($newCandidate));
        $candidateUser->notify(new CandidateCreatedNotification
        ($newCandidate));
    }
}

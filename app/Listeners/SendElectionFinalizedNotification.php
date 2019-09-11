<?php

namespace App\Listeners;

use App\Events\ElectionFinalized;
use App\Notifications\ElectionFinalizedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendElectionFinalizedNotification
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
     * @param  ElectionFinalized $event
     * @return void
     */
    public function handle(ElectionFinalized $event)
    {
        $users = User::all();
        Notification::send($users, new ElectionFinalizedNotification($event->election));
    }
}

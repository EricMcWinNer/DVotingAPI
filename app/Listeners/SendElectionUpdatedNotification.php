<?php

namespace App\Listeners;

use App\Events\ElectionUpdated;
use App\Notifications\ElectionUpdatedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendElectionUpdatedNotification
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
     * @param  ElectionUpdated $event
     * @return void
     */
    public function handle(ElectionUpdated $event)
    {
        $users = User::all();
        Notification::send($users, new ElectionUpdatedNotification($event->election));
    }
}

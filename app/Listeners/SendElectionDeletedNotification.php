<?php

namespace App\Listeners;

use App\Events\ElectionDeleted;
use App\Notifications\ElectionDeletedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendElectionDeletedNotification
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
     * @param  ElectionDeleted $event
     * @return void
     */
    public function handle(ElectionDeleted $event)
    {
        $users = User::all();
        Notification::send($users, new ElectionDeletedNotification($event->election));
    }
}

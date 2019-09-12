<?php

namespace App\Listeners;

use App\Events\OfficerDeleted;
use App\Notifications\OfficerDeletedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOfficerDeletedNotification
{
    /**
     * The name of the connection the job should be sent to
     *
     * @var string
     */
    public $connection = 'database';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     *  The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;


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
     * @param  OfficerDeleted  $event
     * @return void
     */
    public function handle(OfficerDeleted $event)
    {
        $users = User::whereJsonContains('roles', 'officer')->get();
        $user = User::find($event->officer->id);
        $user->notify(new OfficerDeletedNotification($event->officer));
        Notification::send($users, new OfficerDeletedNotification($event->officer));
    }
}

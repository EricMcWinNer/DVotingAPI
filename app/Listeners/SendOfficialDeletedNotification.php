<?php

namespace App\Listeners;

use App\Events\OfficialDeleted;
use App\Notifications\OfficialDeletedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOfficialDeletedNotification implements ShouldQueue
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
     * @param  OfficialDeleted  $event
     * @return void
     */
    public function handle(OfficialDeleted $event)
    {
        $users = User::whereJsonContains('roles', 'official')->get();
        $user = User::find($event->official->id);
        $user->notify(new OfficialDeletedNotification($event->official));
        Notification::send($users, new OfficialDeletedNotification($event->official));
    }
}

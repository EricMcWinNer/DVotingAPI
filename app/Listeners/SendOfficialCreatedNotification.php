<?php

namespace App\Listeners;

use App\Events\OfficialCreated;
use App\Notifications\OfficerCreatedNotification;
use App\Notifications\OfficialCreatedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOfficialCreatedNotification implements ShouldQueue
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
     * @param  OfficialCreated $event
     * @return void
     */
    public function handle(OfficialCreated $event)
    {
        $users = User::whereJsonContains('roles', 'official')->get();
        $user = User::find($event->official->id);
        $user->notify(new OfficialCreatedNotification($event->official));
        Notification::send($users, new OfficialCreatedNotification($event->official));
    }
}

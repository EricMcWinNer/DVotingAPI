<?php

namespace App\Listeners;

use App\Events\CandidateDeleted;
use App\Notifications\CandidateDeletedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendCandidateDeletedNotification implements ShouldQueue
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
    public $queue = 'candidate_listeners';

    /**
     *  The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;


    /**
     * The time (seconds) during which the job would be processed
     *
     * @var int
     */
    public $timeout = 300;


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
     * @param  CandidateDeleted $event
     * @return void
     */
    public function handle(CandidateDeleted $event)
    {
        $users = User::whereJsonContains('roles', "official")->get();
        User::find($event->candidate->user_id)
            ->notify(new CandidateDeletedNotification($event->candidate));
        Notification::send($users, new CandidateDeletedNotification($event->candidate));
    }
}
